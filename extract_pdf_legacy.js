const fs = require('fs');
const pdfjsLib = require('pdfjs-dist/legacy/build/pdf');

const filePath = 'C:\\Users\\oussa\\Downloads\\Telegram Desktop\\Final_Assignment_Attendance_System.pdf';

async function extractPDF() {
  try {
    const fileBuffer = fs.readFileSync(filePath);
    const pdf = await pdfjsLib.getDocument({ data: fileBuffer }).promise;
    
    let fullText = '';
    
    for (let i = 1; i <= pdf.numPages; i++) {
      const page = await pdf.getPage(i);
      const textContent = await page.getTextContent();
      const pageText = textContent.items.map(item => item.str).join(' ');
      fullText += pageText + '\n\n';
    }
    
    console.log('=== PDF CONTENT ===\n');
    console.log(fullText);
    console.log('\n=== END OF PDF ===');
  } catch (err) {
    console.error('Error extracting PDF:', err.message);
    process.exit(1);
  }
}

extractPDF();
