const fs = require('fs');
const path = require('path');

function removeTxtFiles(dir) {
  if (!fs.existsSync(dir)) return;
  
  const files = fs.readdirSync(dir);
  
  files.forEach(file => {
    const filePath = path.join(dir, file);
    const stat = fs.statSync(filePath);
    
    if (stat.isDirectory()) {
      removeTxtFiles(filePath);
    } else if (path.extname(file) === '.txt' && 
               !file.includes('robots') && 
               !file.includes('sitemap')) {
      fs.unlinkSync(filePath);
      console.log(`Removed: ${filePath}`);
    }
  });
}

removeTxtFiles(path.join(__dirname, 'out'));
console.log('All unnecessary .txt files removed from build');