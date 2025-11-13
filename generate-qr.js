const QRCode = require('qrcode-svg');
const fs = require('fs');

function generateProfessionalQR() {
  const config = {
    content: "https://emergesocialcare.co.uk",
    padding: 4,
    width: 400,
    height: 400,
    color: "#2D3748",
    background: "transparent",
    ecl: "H",                
    join: true,
    pretty: true,
    title: "emergesocialcare - Secure Payments",
    description: "Scan to experience seamless payments with emergesocialcare"
  };

  const qrcode = new QRCode(config);
  let svg = qrcode.svg();
  
  fs.writeFileSync('./assets/emergesocialcare-qr-code.svg', svg);
  console.log("Professional emergesocialcare QR code generated: emergesocialcare-qr-code.svg");
}

generateProfessionalQR();