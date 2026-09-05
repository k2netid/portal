<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ __('shield.challenge.title') }} | {{ config('app.name') }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style nonce="{{ request()->cspNonce() }}">
        :root {
            --primary: #0f172a;
            --secondary: #1e293b;
            --accent: #3b82f6;
            --accent-glow: rgba(59, 130, 246, 0.5);
            --text-main: #f8fafc;
            --text-muted: #94a3b8;
            --glass: rgba(30, 41, 59, 0.7);
            --glass-border: rgba(255, 255, 255, 0.1);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background-color: var(--primary);
            background-image: 
                radial-gradient(circle at 0% 0%, rgba(59, 130, 246, 0.15) 0%, transparent 50%),
                radial-gradient(circle at 100% 100%, rgba(59, 130, 246, 0.1) 0%, transparent 50%);
            color: var(--text-main);
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            overflow: hidden;
            padding: 1.5rem;
        }

        /* Abstract mesh background animation */
        .mesh {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: -1;
            opacity: 0.5;
            background: 
                radial-gradient(at 10% 20%, rgba(15, 23, 42, 1) 0%, transparent 50%),
                radial-gradient(at 90% 80%, rgba(59, 130, 246, 0.05) 0%, transparent 50%);
        }

        .container {
            max-width: 480px;
            width: 100%;
            background: var(--glass);
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
            padding: 3rem 2.5rem;
            border-radius: 2rem;
            border: 1px solid var(--glass-border);
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);
            text-align: center;
            position: relative;
            transform: translateY(0);
            transition: transform 0.3s ease;
        }

        .logo-section {
            margin-bottom: 2.5rem;
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        .shield-wrapper {
            width: 80px;
            height: 80px;
            background: rgba(59, 130, 246, 0.1);
            border: 1px solid rgba(59, 130, 246, 0.2);
            border-radius: 1.5rem;
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--accent);
            margin-bottom: 1.25rem;
            position: relative;
        }

        .shield-wrapper::after {
            content: '';
            position: absolute;
            width: 100%;
            height: 100%;
            border-radius: 1.5rem;
            border: 1px solid var(--accent);
            animation: pulse 2s infinite;
        }

        @keyframes pulse {
            0% { transform: scale(1); opacity: 0.5; }
            100% { transform: scale(1.4); opacity: 0; }
        }

        h1 {
            font-size: 1.75rem;
            font-weight: 700;
            margin-bottom: 0.75rem;
            letter-spacing: -0.025em;
            background: linear-gradient(to bottom right, #fff, #94a3b8);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        p {
            font-size: 0.9375rem;
            line-height: 1.6;
            color: var(--text-muted);
            margin-bottom: 2.5rem;
        }

        /* Step Indicators */
        .steps {
            display: flex;
            justify-content: space-between;
            margin-bottom: 2.5rem;
            position: relative;
            padding: 0 1rem;
        }

        .steps::before {
            content: '';
            position: absolute;
            top: 12px;
            left: 2rem;
            right: 2rem;
            height: 2px;
            background: rgba(255, 255, 255, 0.05);
            z-index: 0;
        }

        .step {
            position: relative;
            z-index: 1;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 0.75rem;
            width: 80px;
        }

        .step-dot {
            width: 26px;
            height: 26px;
            border-radius: 50%;
            background: var(--secondary);
            border: 2px solid var(--glass-border);
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.4s ease;
        }

        .step-dot svg {
            width: 14px;
            height: 14px;
            color: white;
            opacity: 0;
            transform: scale(0.5);
            transition: all 0.3s ease;
        }

        .step-label {
            font-size: 0.75rem;
            font-weight: 600;
            color: var(--text-muted);
            text-transform: uppercase;
            letter-spacing: 0.05em;
            transition: color 0.3s ease;
        }

        .step.active .step-dot {
            background: var(--accent);
            border-color: var(--accent);
            box-shadow: 0 0 15px var(--accent-glow);
        }

        .step.active .step-label {
            color: var(--accent);
        }

        .step.done .step-dot {
            background: var(--accent);
            border-color: var(--accent);
        }

        .step.done .step-dot svg {
            opacity: 1;
            transform: scale(1);
        }

        .status-badge {
            display: inline-flex;
            align-items: center;
            padding: 0.5rem 1rem;
            background: rgba(255, 255, 255, 0.03);
            border: 1px solid var(--glass-border);
            border-radius: 2rem;
            font-size: 0.8125rem;
            font-weight: 500;
            color: var(--text-muted);
            margin-bottom: 1.5rem;
        }

        .dot-pulse {
            width: 8px;
            height: 8px;
            background: var(--accent);
            border-radius: 50%;
            margin-right: 0.75rem;
            animation: dot-pulse 1.5s infinite;
        }

        @keyframes dot-pulse {
            0% { opacity: 1; transform: scale(1); }
            50% { opacity: 0.4; transform: scale(0.8); }
            100% { opacity: 1; transform: scale(1); }
        }

        .btn-retry {
            display: none;
            width: 100%;
            padding: 0.875rem;
            background: var(--accent);
            color: white;
            border: none;
            border-radius: 0.875rem;
            font-weight: 600;
            font-size: 0.9375rem;
            cursor: pointer;
            transition: all 0.2s ease;
            margin-top: 1rem;
        }

        .btn-retry:hover {
            filter: brightness(1.1);
            transform: translateY(-1px);
        }

        .footer {
            margin-top: 2rem;
            padding-top: 2rem;
            border-top: 1px solid rgba(255, 255, 255, 0.05);
            font-size: 0.75rem;
            color: var(--text-muted);
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 1.5rem;
        }

        .footer span {
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        /* Honeypot fields */
        .hp-field {
            position: absolute;
            left: -9999px;
            top: -9999px;
            opacity: 0;
            z-index: -1;
        }
    </style>
</head>
<body>
    <div class="mesh"></div>
    
    <div class="container">
        <div class="logo-section">
            <div class="shield-wrapper">
                <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>
                </svg>
            </div>
            <h1>{{ __('shield.challenge.title') }}</h1>
            <p>{{ __('shield.challenge.message') }}</p>
        </div>

        <div class="steps">
            <div class="step active" id="step-1">
                <div class="step-dot">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="4" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"></polyline></svg>
                </div>
                <div class="step-label">{{ __('shield.challenge.steps.analyze') }}</div>
            </div>
            <div class="step" id="step-2">
                <div class="step-dot">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="4" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"></polyline></svg>
                </div>
                <div class="step-label">{{ __('shield.challenge.steps.solve') }}</div>
            </div>
            <div class="step" id="step-3">
                <div class="step-dot">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="4" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"></polyline></svg>
                </div>
                <div class="step-label">{{ __('shield.challenge.steps.verify') }}</div>
            </div>
        </div>
        
        <div class="status-badge">
            <div class="dot-pulse"></div>
            <span id="status-text">{{ __('shield.challenge.status.initializing') }}</span>
        </div>

        <button id="retry-btn" class="btn-retry" onclick="window.location.reload()">
            {{ __('shield.challenge.retry') }}
        </button>

        <form id="challenge-form" style="display:none">
            @csrf
            <input type="text" name="_hp_email" class="hp-field" tabindex="-1" autocomplete="off">
            <input type="text" name="_hp_subject" class="hp-field" tabindex="-1" autocomplete="off">
            <input type="hidden" name="nonce" value="{{ $nonce }}">
            <input type="hidden" name="redirect_to" value="{{ $redirectTo }}">
            <input type="hidden" name="solution" id="solution-input">
        </form>

        <div class="footer">
            <span>
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
                Bot Shield Active
            </span>
            <span>&copy; {{ date('Y') }} <a href="https://jejakawan.com" target="_blank" style="color: inherit; text-decoration: none;" onmouseover="this.style.color='var(--accent)'" onmouseout="this.style.color='inherit'">Portal</a>. All rights reserved.</span>
            <span>Developed by <a href="https://jejakawan.com" target="_blank" style="color: inherit; text-decoration: none; font-weight: 600;" onmouseover="this.style.color='var(--accent)'" onmouseout="this.style.color='inherit'">jejakawan</a></span>
        </div>
    </div>

    <script nonce="{{ request()->cspNonce() }}">
        const nonce = "{{ $nonce }}";
        const difficulty = {{ $difficulty }};
        const target = "0".repeat(difficulty);
        const TIMEOUT_MS = 60000; // 60 second timeout
        
        const statusEl = document.getElementById('status-text');
        const retryBtn = document.getElementById('retry-btn');
        const steps = {
            1: document.getElementById('step-1'),
            2: document.getElementById('step-2'),
            3: document.getElementById('step-3')
        };

        function setStep(stepNum, isDone = false) {
            Object.keys(steps).forEach(s => {
                steps[s].classList.remove('active');
                if (s < stepNum) steps[s].classList.add('done');
            });
            steps[stepNum].classList.add('active');
            if (isDone) steps[stepNum].classList.add('done');
        }

        function formatNumber(n) {
            if (n >= 1000000) return (n / 1000000).toFixed(1) + 'M';
            if (n >= 1000) return (n / 1000).toFixed(1) + 'K';
            return n.toString();
        }

        async function solvePoW() {
            const usePolyfill = !window.crypto || !window.crypto.subtle;

            // Step 1: Analyze
            statusEl.innerText = "{{ __('shield.challenge.status.analyzing') }}";
            await new Promise(r => setTimeout(r, 800));
            setStep(2);

            // Step 2: Solve with multi-threaded workers
            statusEl.innerText = "{{ __('shield.challenge.status.verifying') }}";

            const numWorkers = Math.min(navigator.hardwareConcurrency || 4, 16);
            
            // Each worker searches a partitioned range to avoid overlap
            // Worker 0: 0, numWorkers, 2*numWorkers, ...
            // Worker 1: 1, numWorkers+1, 2*numWorkers+1, ...
            const workerCode = `
                // Tiny SHA-256 polyfill for HTTP/non-secure contexts
                function sha256(ascii) {
                    function rightRotate(value, amount) { return (value>>>amount) | (value<<(32 - amount)); }
                    var mathPow = Math.pow;
                    var maxWord = mathPow(2, 32);
                    var lengthProperty = 'length'
                    var i, j;
                    var result = ''
                    var words = [];
                    var asciiBitLength = ascii[lengthProperty]*8;
                    var hash = sha256.h = sha256.h || [];
                    var k = sha256.k = sha256.k || [];
                    var primeCounter = k[lengthProperty];
                    var isComposite = {};
                    for (var candidate = 2; primeCounter < 64; candidate++) {
                        if (!isComposite[candidate]) {
                            for (i = 0; i < 313; i += candidate) isComposite[i] = candidate;
                            hash[primeCounter] = (mathPow(candidate, .5)*maxWord)|0;
                            k[primeCounter++] = (mathPow(candidate, 1/3)*maxWord)|0;
                        }
                    }
                    ascii += '\\x80';
                    while (ascii[lengthProperty]%64 - 56) ascii += '\\x00';
                    for (i = 0; i < ascii[lengthProperty]; i++) {
                        j = ascii.charCodeAt(i);
                        if (j>>8) return;
                        words[i>>2] |= j << ((3 - i)%4)*8;
                    }
                    words[words[lengthProperty]] = ((asciiBitLength/maxWord)|0);
                    words[words[lengthProperty]] = (asciiBitLength)
                    for (j = 0; j < words[lengthProperty];) {
                        var w = words.slice(j, j += 16);
                        var oldHash = hash;
                        hash = hash.slice(0, 8);
                        for (i = 0; i < 64; i++) {
                            var i2 = i + j;
                            var w15 = w[i - 15], w2 = w[i - 2];
                            var a = hash[0], e = hash[4];
                            var temp1 = hash[7] + (rightRotate(e, 6) ^ rightRotate(e, 11) ^ rightRotate(e, 25)) + ((e&hash[5])^((~e)&hash[6])) + k[i] + (w[i] = (i < 16) ? w[i] : (w[i - 16] + (rightRotate(w15, 7) ^ rightRotate(w15, 18) ^ (w15>>>3)) + w[i - 7] + (rightRotate(w2, 17) ^ rightRotate(w2, 19) ^ (w2>>>10)))|0);
                            var temp2 = (rightRotate(a, 2) ^ rightRotate(a, 13) ^ rightRotate(a, 22)) + ((a&hash[1])^(a&hash[2])^(hash[1]&hash[2]));
                            hash = [(temp1 + temp2)|0].concat(hash);
                            hash[4] = (hash[4] + temp1)|0;
                        }
                        for (i = 0; i < 8; i++) hash[i] = (hash[i] + oldHash[i])|0;
                    }
                    var hex = [];
                    for (i = 0; i < 8; i++) {
                        for (j = 3; j + 1; j--) {
                            var b = (hash[i]>>(j*8))&255;
                            hex.push(b);
                        }
                    }
                    return new Uint8Array(hex);
                }

                self.onmessage = async function(e) {
                    const { nonce, target, workerId, totalWorkers, usePolyfill } = e.data;
                    const encoder = new TextEncoder();
                    let solution = workerId;
                    let checked = 0;
                    const BATCH = 1000;
                    
                    while (true) {
                        let hashArray;
                        if (usePolyfill) {
                            hashArray = sha256(nonce + solution);
                        } else {
                            const data = encoder.encode(nonce + solution);
                            const hashBuffer = await crypto.subtle.digest('SHA-256', data);
                            hashArray = new Uint8Array(hashBuffer);
                        }
                        
                        // Fast prefix check: compare bytes directly for leading zero hex chars
                        let match = true;
                        const fullBytes = Math.floor(target.length / 2);
                        for (let i = 0; i < fullBytes; i++) {
                            if (hashArray[i] !== 0) { match = false; break; }
                        }
                        if (match && (target.length % 2 === 1)) {
                            // Check upper nibble of the next byte
                            if ((hashArray[fullBytes] >> 4) !== 0) match = false;
                        }
                        
                        if (match) {
                            self.postMessage({ type: 'solved', solution: solution });
                            return;
                        }
                        
                        solution += totalWorkers;
                        checked++;
                        
                        if (checked % BATCH === 0) {
                            self.postMessage({ type: 'progress', checked: checked });
                        }
                    }
                };
            `;

            const blob = new Blob([workerCode], { type: 'application/javascript' });
            const blobUrl = URL.createObjectURL(blob);
            
            let solved = false;
            let totalChecked = 0;
            const startTime = Date.now();
            const workers = [];

            // Progress update interval
            const progressInterval = setInterval(() => {
                if (solved) return;
                const elapsed = ((Date.now() - startTime) / 1000).toFixed(1);
                const rate = totalChecked > 0 ? formatNumber(Math.round(totalChecked / (elapsed || 1))) : '0';
                statusEl.innerText = `Solving... ${elapsed}s · ${formatNumber(totalChecked)} hashes · ${rate}/s`;
            }, 500);

            // Timeout handler
            const timeoutId = setTimeout(() => {
                if (!solved) {
                    solved = true;
                    clearInterval(progressInterval);
                    workers.forEach(w => w.terminate());
                    URL.revokeObjectURL(blobUrl);
                    statusEl.innerText = "Verification timed out. Please retry.";
                    retryBtn.style.display = 'block';
                }
            }, TIMEOUT_MS);

            return new Promise((resolve) => {
                for (let i = 0; i < numWorkers; i++) {
                    const worker = new Worker(blobUrl);
                    workers.push(worker);

                    worker.onmessage = (e) => {
                        if (e.data.type === 'progress') {
                            totalChecked += e.data.checked;
                            // Reset worker's counter via the checked being cumulative per-report
                            return;
                        }

                        if (e.data.type === 'solved' && !solved) {
                            solved = true;
                            clearInterval(progressInterval);
                            clearTimeout(timeoutId);

                            // Terminate all workers
                            workers.forEach(w => w.terminate());
                            URL.revokeObjectURL(blobUrl);

                            setStep(3);
                            statusEl.innerText = "{{ __('shield.challenge.status.finalizing') }}";
                            submitSolution(e.data.solution);
                            resolve();
                        }
                    };

                    worker.postMessage({ nonce, target, workerId: i, totalWorkers: numWorkers, usePolyfill });
                }
            });
        }

        async function submitSolution(solution) {
            const form = document.getElementById('challenge-form');
            const solutionInput = document.getElementById('solution-input');
            solutionInput.value = solution;

            try {
                const formData = new FormData(form);
                const response = await fetch('/api/v1/security/verify-connection', {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    }
                });

                if (response.status === 419) {
                    statusEl.innerText = "Session expired. Refreshing...";
                    setTimeout(() => window.location.reload(), 1500);
                    return;
                }

                const data = await response.json();

                if (data.success && data.data.verified) {
                    setStep(3, true);
                    statusEl.innerText = "{{ __('shield.challenge.status.verified') }}";
                    setTimeout(() => {
                        window.location.href = data.data.redirect_to || '/';
                    }, 500);
                } else {
                    statusEl.innerText = data.message || "{{ __('shield.challenge.status.failed') }}";
                    retryBtn.style.display = 'block';
                }
            } catch (error) {
                console.error('Final verification error:', error);
                statusEl.innerText = "Connection error. Please try again.";
                retryBtn.style.display = 'block';
            }
        }

        window.onload = () => setTimeout(solvePoW, 600);
    </script>
</body>
</html>
