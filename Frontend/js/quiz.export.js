// quiz.export.js
export function generatePDF(score, total, message) {
  const { jsPDF } = window.jspdf;
  const doc = new jsPDF();

  const name = document.getElementById("participantName")?.value || "Participant inconnu";
  const now = new Date();
  const dateStr = now.toLocaleDateString("fr-FR");
  const timeStr = now.toLocaleTimeString("fr-FR");
  const certId = "RSE-" + Math.floor(100000 + Math.random() * 900000); // Numéro unique

  // 🖼️ Fond décoratif
  const background = new Image();
  background.src = 'assets/images/certificat_background.jpg';
  background.onload = () => {
    doc.addImage(background, 'JPEG', 0, 0, 210, 297); // Pleine page A4

    // 📄 Contenu principal
    doc.setFontSize(20);
    doc.text("CERTIFICAT DE RÉUSSITE", 60, 40);

    doc.setFontSize(12);
    doc.text(`Ce certificat est décerné à :`, 20, 60);
    doc.setFontSize(16);
    doc.text(`${name}`, 20, 70);

    doc.setFontSize(12);
    doc.text(`Pour avoir complété avec succès le Quiz RSE`, 20, 85);
    doc.text(`Date : ${dateStr}`, 20, 95);
    doc.text(`Heure : ${timeStr}`, 20, 105);
    doc.text(`Score : ${score} / ${total}`, 20, 115);
    doc.text(`Commentaire : ${message}`, 20, 125);
    doc.text(`Numéro de validation : ${certId}`, 20, 135);

    // 🔗 QR code vers les résultats en ligne
    const qrUrl = `https://rse-website.com/mes_quiz.html?user=${encodeURIComponent(name)}`;
    const qr = new Image();
    qr.src = `https://api.qrserver.com/v1/create-qr-code/?size=100x100&data=${encodeURIComponent(qrUrl)}`;
    qr.onload = () => {
      doc.addImage(qr, 'PNG', 20, 250, 40, 40);
      doc.text("🔗 Voir mes résultats en ligne", 70, 265);

      // ✒️ Signature numérique
      const signature = new Image();
      signature.src = 'assets/images/signature.png';
      signature.onload = () => {
        doc.addImage(signature, 'PNG', 130, 250, 50, 20);
        doc.text("Signature", 130, 245);

        // 🖼️ Logo en haut à droite
        const logo = new Image();
        logo.src = 'assets/images/logo.png';
        logo.onload = () => {
          doc.addImage(logo, 'PNG', 150, 10, 40, 20);
          doc.save("certificat_RSE.pdf");
        };
      };
    };
  };
}

export function exportAnswers() {
  const selectedAnswers = document.querySelectorAll('input[type="radio"]:checked');
  const doc = new jspdf.jsPDF();

  doc.setFontSize(14);
  doc.text("Mes réponses au Quiz RSE", 20, 20);

  let y = 30;
  selectedAnswers.forEach((input, index) => {
    const question = input.closest("fieldset").querySelector("legend").textContent;
    const answer = input.value;
    doc.text(`${index + 1}. ${question}`, 20, y);
    y += 10;
    doc.text(`→ Réponse : ${answer}`, 25, y);
    y += 15;
  });

  doc.save("mes_reponses_quiz.pdf");
}