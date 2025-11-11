import { exportExcel } from './exportExcel.js';

process.on('message', async data => {
  await exportExcel(data);
  process.send('✅ Export terminé');
});