export function downloadPDF() {
  window.open('/rapport-pdf', '_blank');
}

export function downloadExcel() {
  const token = localStorage.getItem('jwt');
  window.open(`/export-excel?token=${token}`, '_blank');
}