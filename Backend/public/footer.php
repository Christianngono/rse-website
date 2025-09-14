<footer>
    <h3>Historique des notifications</h3>
  <div style="margin-bottom: 10px;">
    <button onclick="clearNotificationLog()">Effacer l’historique</button>
    <button onclick="exportNotificationLog()">Exporter en .txt</button>
  </div>
  <ul id="notificationLog"></ul>
  <p>&copy; <?= date('Y') ?> Quiz RSE | Conçu avec passion </p>
  <ul>
    <li><a href="#">Mentions légales</a></li>
    <li><a href="#">Politique de confidentialité</a></li>
    <li><a href="https://github.com/Christianngono/rse-website" target="_blank">GitHub</a></li>
  </ul> 
</footer>