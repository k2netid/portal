<?php

declare(strict_types=1);

namespace Modules\Core\System\Services;

use Exception;
use Illuminate\Support\Facades\File;
use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\BinaryOp\Concat;
use PhpParser\Node\Expr\ClassConstFetch;
use PhpParser\Node\Expr\ConstFetch;
use PhpParser\Node\Expr\Eval_;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\Include_;
use PhpParser\Node\Expr\ShellExec;
use PhpParser\Node\Name;
use PhpParser\Node\Scalar\MagicConst\Dir;
use PhpParser\Node\Scalar\String_;
use PhpParser\NodeTraverser;
use PhpParser\NodeVisitorAbstract;
use PhpParser\ParserFactory;
use ZipArchive;

class ExtensionSecurityScanner
{
    /**
     * @var list<string>
     */
    protected array $bannedFunctions = [
        // Shell/System Executions
        'exec',
        'shell_exec',
        'system',
        'passthru',
        'popen',
        'proc_open',
        'pcntl_exec',
        'assert',
        'create_function',
        'dl',

        // Low-level network socket bypassing
        'fsockopen',
        'pfsockopen',
        'stream_socket_client',
        'curl_exec',
        'curl_multi_exec',

        // Raw, unsandboxed filesystem mutations (encouraging Storage facades)
        'file_put_contents',
        'fwrite',
        'touch',
        'unlink',
        'mkdir',
        'rmdir',
        'rename',
        'copy',
    ];

    /**
     * Scan all PHP files inside a ZIP archive using the AST parser.
     */
    public function scanZip(string $zipPath): void
    {
        $zip = new ZipArchive;
        if ($zip->open($zipPath) !== true) {
            throw new Exception('Gagal membuka file paket ZIP.');
        }

        for ($i = 0; $i < $zip->numFiles; $i++) {
            $filename = $zip->getNameIndex($i);

            if (is_string($filename) && pathinfo($filename, PATHINFO_EXTENSION) === 'php') {
                $content = $zip->getFromIndex($i);
                if ($content !== false) {
                    $this->scanCode($content, $filename);
                }
            }
        }

        $zip->close();
    }

    /**
     * Scan all PHP files inside a directory recursively using the AST parser.
     */
    public function scanDirectory(string $dirPath): void
    {
        if (! is_dir($dirPath)) {
            throw new Exception("Direktori tidak ditemukan: {$dirPath}");
        }

        $files = File::allFiles($dirPath);
        foreach ($files as $file) {
            if ($file->getExtension() === 'php') {
                $realPath = $file->getRealPath();
                if (! is_string($realPath)) {
                    continue;
                }
                $content = File::get($realPath);
                $this->scanCode($content, $file->getRelativePathname());
            }
        }
    }

    /**
     * Parse and traverse a single PHP code string to check for AST violations.
     */
    public function scanCode(string $code, string $filePath = 'unknown'): void
    {
        try {
            $parser = (new ParserFactory)->createForNewestSupportedVersion();
            $stmts = $parser->parse($code);

            if ($stmts === null) {
                return;
            }

            $traverser = new NodeTraverser;
            $visitor = new class($this->bannedFunctions, $filePath) extends NodeVisitorAbstract
            {
                /** @var list<string> */
                public array $violations = [];

                /**
                 * @param  list<string>  $bannedFunctions
                 */
                public function __construct(
                    protected array $bannedFunctions,
                    protected string $filePath
                ) {}

                public function enterNode(Node $node)
                {
                    // 1. Detect 'eval()' construct
                    if ($node instanceof Eval_) {
                        $this->violations[] = "Security Gate Violation: Penggunaan konstruksi berbahaya 'eval()' terdeteksi di baris {$node->getStartLine()} pada file: {$this->filePath}";
                    }

                    // 2. Detect shell backtick operators (e.g. `ls -la`)
                    if ($node instanceof ShellExec) {
                        $this->violations[] = "Security Gate Violation: Penggunaan operator backtick shell terdeteksi di baris {$node->getStartLine()} pada file: {$this->filePath}";
                    }

                    // 3. Detect system execution function calls
                    if ($node instanceof FuncCall) {
                        // Dynamic function invocation (e.g., $func())
                        if ($node->name instanceof Expr) {
                            $this->violations[] = "Security Gate Violation: Eksekusi fungsi dinamis terdeteksi di baris {$node->getStartLine()} pada file: {$this->filePath}. Pola ini dilarang untuk mencegah penyuntingan kode tersembunyi.";
                        } elseif ($node->name instanceof Name) {
                            $funcName = strtolower($node->name->toString());

                            // Detect call_user_func and call_user_func_array bypasses
                            if ($funcName === 'call_user_func' || $funcName === 'call_user_func_array') {
                                if (isset($node->args[0]) && $node->args[0] instanceof Arg) {
                                    $firstArg = $node->args[0]->value;
                                    if ($firstArg instanceof String_) {
                                        $targetFunc = strtolower($firstArg->value);
                                        if (in_array($targetFunc, $this->bannedFunctions)) {
                                            $this->violations[] = "Security Gate Violation: Pemanggilan tidak langsung fungsi terlarang '{$targetFunc}()' melalui call_user_func terdeteksi di baris {$node->getStartLine()} pada file: {$this->filePath}";
                                        }
                                    } else {
                                        $this->violations[] = "Security Gate Violation: Pemanggilan dinamis berbahaya melalui call_user_func terdeteksi di baris {$node->getStartLine()} pada file: {$this->filePath}";
                                    }
                                }
                            }

                            if (in_array($funcName, $this->bannedFunctions)) {
                                $this->violations[] = "Security Gate Violation: Panggilan fungsi sistem terlarang '{$funcName}()' terdeteksi di baris {$node->getStartLine()} pada file: {$this->filePath}";
                            }
                        }
                    }

                    // 4. Detect dynamic include/require bypass vectors
                    if ($node instanceof Include_) {
                        if (! $this->isSafeIncludeExpr($node->expr)) {
                            $this->violations[] = "Security Gate Violation: Penggunaan pernyataan 'include/require' dinamis berbahaya terdeteksi di baris {$node->getStartLine()} pada file: {$this->filePath}. Pola ini dilarang untuk mencegah injeksi file luar.";
                        }
                    }

                    return null;
                }

                /**
                 * Helper to statically check if include expression is fully safe (literal strings or __DIR__ only)
                 */
                protected function isSafeIncludeExpr(Expr $expr): bool
                {
                    if ($expr instanceof String_) {
                        return true;
                    }
                    if ($expr instanceof ConstFetch && in_array(strtolower($expr->name->toString()), ['true', 'false', 'null'])) {
                        return true;
                    }
                    if ($expr instanceof ClassConstFetch) {
                        return true;
                    }
                    if ($expr instanceof Concat) {
                        return $this->isSafeIncludeExpr($expr->left) && $this->isSafeIncludeExpr($expr->right);
                    }
                    if ($expr instanceof Dir || $expr instanceof Node\Scalar\MagicConst\File) {
                        return true;
                    }

                    return false;
                }
            };

            $traverser->addVisitor($visitor);
            $traverser->traverse($stmts);

            if (! empty($visitor->violations)) {
                throw new Exception($visitor->violations[0]);
            }

        } catch (Exception $e) {
            // Rethrow or wrap parsing exceptions to secure the upload
            throw new Exception("Analisis Keamanan Gagal pada {$filePath}: ".$e->getMessage());
        }
    }
}
