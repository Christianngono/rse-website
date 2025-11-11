export function animateViewTransition() {
  const svg = document.createElementNS('http://www.w3.org/2000/svg', 'svg');
  svg.setAttribute('viewBox', '0 0 1440 320');
  svg.setAttribute('preserveAspectRatio', 'none');
  svg.style.width = '100%';
  svg.style.height = '80px';

  const path = document.createElementNS('http://www.w3.org/2000/svg', 'path');
  path.setAttribute('fill', '#00bfff');
  path.setAttribute('fill-opacity', '0.3');
  path.innerHTML = `
    <animate attributeName="d" dur="2s" repeatCount="1"
      values="
        M0,160L80,165.3C160,171,320,181,480,176C640,171,800,149,960,144C1120,139,1280,149,1360,154.7L1440,160L1440,320L0,320Z;
        M0,180L80,185.3C160,191,320,201,480,196C640,191,800,169,960,164C1120,159,1280,169,1360,174.7L1440,180L1440,320L0,320Z;
        M0,160L80,165.3C160,171,320,181,480,176C640,171,800,149,960,144C1120,139,1280,149,1360,154.7L1440,160L1440,320L0,320Z
      " />
  `;
  svg.appendChild(path);
  document.getElementById('app').prepend(svg);

  setTimeout(() => svg.remove(), 2000);
}