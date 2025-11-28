const fs = require('fs');
const pdfParse = require('pdf-parse');

const filePath = 'C:\\Users\\oussa\\Downloads\\Telegram Desktop\\Final_Assignment_Attendance_System.pdf';

fs.readFile(filePath, async (err, dataBuffer) => {
  if (err) {
    console.error('Error reading file:', err);
    process.exit(1);
  }

  try {
    const data = await pdfParse(dataBuffer);
    console.log('=== PDF CONTENT ===\n');
    console.log(data.text);
    console.log('\n=== END OF PDF ===');
  } catch (err) {
    console.error('Error parsing PDF:', err);
    process.exit(1);
  }
});
