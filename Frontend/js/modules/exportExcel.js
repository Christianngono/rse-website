import ExcelJS from 'exceljs';
import fs from 'fs';

export async function exportExcel(data, filePath = 'Frontend/assets/export.xlsx') {
  const workbook = new ExcelJS.Workbook();
  const sheet = workbook.addWorksheet('Images encodées');

  sheet.columns = [
    { header: 'Nom', key: 'name', width: 30 },
    { header: 'Taille', key: 'size', width: 15 },
    { header: 'Dimensions', key: 'dimensions', width: 20 }
  ];

  data.forEach(img => {
    sheet.addRow({
      name: img.name,
      size: img.size,
      dimensions: `${img.width}x${img.height}`
    });
  });

  // Ajout d’un graphique (barres)
  const chartSheet = workbook.addWorksheet('Graphique');
  chartSheet.getCell('A1').value = 'Nom';
  chartSheet.getCell('B1').value = 'Taille (Ko)';
  data.forEach((img, i) => {
    chartSheet.getCell(`A${i + 2}`).value = img.name;
    chartSheet.getCell(`B${i + 2}`).value = parseFloat(img.size);
  });

  await workbook.xlsx.writeFile(filePath);
}