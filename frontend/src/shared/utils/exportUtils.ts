export const exportToCSV = <T extends Record<string, unknown>>(data: T[], filename: string) => {
  if (data.length === 0 || !data[0]) return;
  
  // Extract headers from the first object
  const headers = Object.keys(data[0]);
  
  // Create CSV rows
  const csvRows = [
    headers.join(','),
    ...data.map(row => 
      headers.map(header => {
        const val = row[header] ?? '';
        // Escape quotes and wrap in quotes
        return `"${String(val).replace(/"/g, '""')}"`;
      }).join(',')
    )
  ];

  const csvString = csvRows.join('\n');
  const blob = new Blob([csvString], { type: 'text/csv;charset=utf-8;' });
  const url = URL.createObjectURL(blob);
  
  const link = document.createElement('a');
  link.setAttribute('href', url);
  link.setAttribute('download', `${filename}.csv`);
  link.style.visibility = 'hidden';
  document.body.appendChild(link);
  link.click();
  document.body.removeChild(link);
};
