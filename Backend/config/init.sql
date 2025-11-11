-- Sélection de la base
USE rse_quiz;

-- Suppression des anciennes tables si elles existent
DROP TABLE IF EXISTS email_logs;
DROP TABLE IF EXISTS activation_tokens;
DROP TABLE IF EXISTS registration_attempts;
DROP TABLE IF EXISTS sms_logs;
DROP TABLE IF EXISTS phone_verifications;
DROP TABLE IF EXISTS signature_tokens;
DROP TABLE IF EXISTS user_answers;
DROP TABLE IF EXISTS questions;
DROP TABLE IF EXISTS users;
DROP TABLE IF EXISTS admin_logs;
DROP TABLE IF EXISTS quiz_scores;
DROP TABLE IF EXISTS report_config;
DROP TABLE IF EXISTS report_history;

-- Table des utilisateurs (administration)
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(255) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    role ENUM('admin', 'user') DEFAULT 'user',
    profil ENUM('novice', 'confirmé', 'expert') DEFAULT 'novice',
    email VARCHAR(255),
    phone VARCHAR(20),
    is_active BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    last_login TIMESTAMP NULL,
    quiz_score INT DEFAULT 0
);

CREATE TABLE admin_logs (
  id INT AUTO_INCREMENT PRIMARY KEY,
  admin_username VARCHAR(255),
  action TEXT,
  target_user_id INT,
  timestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE quiz_scores (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_name VARCHAR(255) NOT NULL,
  score INT NOT NULL,
  total INT NOT NULL,
  message TEXT,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Table des logs d'emails envoyés
CREATE TABLE email_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(255),
    recipient VARCHAR(255),
    subject TEXT,
    sent_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- Table des questions
CREATE TABLE questions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    question_text TEXT NOT NULL,
    correct_answer VARCHAR(255) NOT NULL,
    category VARCHAR(50),
    difficulty ENUM('facile', 'moyen', 'difficile')
);

-- Index pour optimiser les requêtes
CREATE INDEX idx_category ON questions(category);
CREATE INDEX idx_difficulty ON questions(difficulty);

-- Table des réponses des utilisateurs
CREATE TABLE user_answers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_name VARCHAR(255) NOT NULL,
    question_id INT NOT NULL,
    user_answer VARCHAR(255) NOT NULL,
    is_correct BOOLEAN,
    answered_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (question_id) REFERENCES questions(id) ON DELETE CASCADE
);

-- Index pour les réponses
CREATE INDEX idx_user ON user_answers(user_name);

-- Table des tokens d'activation
CREATE TABLE activation_tokens (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(255) NOT NULL,
    token VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE registration_attempts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    ip_address VARCHAR(45),
    username VARCHAR(255),
    attempted_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    success BOOLEAN
);

CREATE TABLE sms_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(255),
    phone VARCHAR(20),
    message TEXT,
    sent_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    status VARCHAR(50)
);

CREATE TABLE phone_verifications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(255) NOT NULL,
    phone VARCHAR(20) NOT NULL,
    code VARCHAR(10),
    verified_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    status ENUM('validé', 'échoué') DEFAULT 'validé'
);

-- Table des tokens de signature numérique
CREATE TABLE signature_tokens (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(255) NOT NULL,
    email VARCHAR(255),
    phone VARCHAR(20),
    code VARCHAR(10) NOT NULL,
    token VARCHAR(255) NOT NULL,
    expires_at DATETIME NOT NULL,
    is_validated BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE report_config (
    id INT PRIMARY KEY,
    email VARCHAR(255),
    frequency VARCHAR(20),
    format VARCHAR(10)
);

CREATE TABLE report_history (
  id INT AUTO_INCREMENT PRIMARY KEY,
  sent_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  recipient VARCHAR(255) NOT NULL,
  format VARCHAR(10) NOT NULL,
  frequency VARCHAR(20) NOT NULL,
  status VARCHAR(10) NOT NULL,
  message TEXT
);


-- Insérer les questions
INSERT INTO questions (question_text, correct_answer, category, difficulty) VALUES
-- Environnement
('Quel gaz est le plus lié à l’effet de serre ?', 'CO2', 'Environnement', 'facile'),
('Quelle énergie est renouvelable parmi les suivantes : charbon, solaire, pétrole ?', 'solaire', 'Environnement', 'facile'),
('Qu’est-ce que la compensation carbone ?', 'investissement écologique', 'Environnement', 'moyen'),
('Quel est l’objectif de la neutralité carbone ?', 'réduire les émissions', 'Environnement', 'moyen'),
('Quel est le rôle de l’économie circulaire ?', 'réduire les déchets', 'Environnement', 'moyen'),
('Que signifie le sigle GES ?', 'gaz à effet de serre', 'Environnement', 'facile'),
('Quel est l’impact principal de la déforestation ?', 'perte de biodiversité', 'Environnement', 'moyen'),
('Quel est le principal gaz émis par les transports ?', 'CO2', 'Environnement', 'facile'),
('Que signifie le label HQE ?', 'haute qualité environnementale', 'Environnement', 'difficile'),
('Quel est le rôle d’un bilan carbone ?', 'mesurer les émissions', 'Environnement', 'moyen'),

-- Social
('Que signifie QVT ?', 'qualité de vie au travail', 'Social', 'facile'),
('Qu’est-ce qu’une politique d’inclusion ?', 'égalité des chances', 'Social', 'moyen'),
('Pourquoi promouvoir la diversité en entreprise ?', 'favoriser l’équité', 'Social', 'facile'),
('Que signifie le droit à la déconnexion ?', 'respect du temps personnel', 'Social', 'moyen'),
('Quel est l’objectif d’un plan d’égalité professionnelle ?', 'réduire les écarts', 'Social', 'moyen'),
('Qu’est-ce que le harcèlement moral ?', 'violence psychologique', 'Social', 'facile'),
('Quel est le rôle des syndicats ?', 'défendre les salariés', 'Social', 'facile'),
('Que signifie le dialogue social ?', 'communication entre employeurs et salariés', 'Social', 'facile'),
('Qu’est-ce qu’un accident du travail ?', 'événement survenu pendant l’activité professionnelle', 'Social', 'facile'),
('Pourquoi mesurer le taux d’absentéisme ?', 'évaluer le climat social', 'Social', 'moyen'),

-- Gouvernance
('Que signifie le terme gouvernance ?', 'mode de décision', 'Gouvernance', 'moyen'),
('Quel est le rôle du comité RSE ?', 'piloter la stratégie responsable', 'Gouvernance', 'difficile'),
('Que signifie la transparence en entreprise ?', 'communication honnête', 'Gouvernance', 'facile'),
('Pourquoi publier un rapport RSE ?', 'informer les parties prenantes', 'Gouvernance', 'moyen'),
('Que signifie la conformité réglementaire ?', 'respect des lois', 'Gouvernance', 'facile'),
('Quel est le rôle du conseil d’administration ?', 'orienter la stratégie', 'Gouvernance', 'moyen'),
('Que signifie le terme audit ?', 'évaluation indépendante', 'Gouvernance', 'facile'),
('Pourquoi mettre en place une charte éthique ?', 'encadrer les comportements', 'Gouvernance', 'moyen'),
('Que signifie le terme parties prenantes ?', 'acteurs impactés par l’entreprise', 'Gouvernance', 'facile'),
('Quel est le rôle d’un indicateur RSE ?', 'mesurer les progrès', 'Gouvernance', 'moyen'),

-- Éthique
('Que signifie l’éthique des affaires ?', 'respect des valeurs', 'Éthique', 'facile'),
('Qu’est-ce qu’un conflit d’intérêts ?', 'intérêt personnel contre intérêt collectif', 'Éthique', 'moyen'),
('Pourquoi lutter contre la corruption ?', 'préserver l’intégrité', 'Éthique', 'moyen'),
('Que signifie le terme greenwashing ?', 'communication trompeuse', 'Éthique', 'facile'),
('Qu’est-ce qu’un comportement responsable ?', 'agir avec respect et équité', 'Éthique', 'facile'),
('Pourquoi respecter les droits humains ?', 'garantir la dignité', 'Éthique', 'facile'),
('Que signifie la traçabilité ?', 'suivi des produits', 'Éthique', 'moyen'),
('Qu’est-ce qu’un fournisseur responsable ?', 'respecte les normes sociales et environnementales', 'Éthique', 'moyen'),
('Pourquoi éviter le travail des enfants ?', 'respect des droits fondamentaux', 'Éthique', 'moyen'),
('Que signifie la loyauté des pratiques ?', 'équité dans les relations commerciales', 'Éthique', 'facile'),

-- Développement durable
('Quels sont les 3 piliers du développement durable ?', 'économie, social, environnement', 'Développement durable', 'facile'),
('Que signifie le terme empreinte écologique ?', 'impact sur la planète', 'Développement durable', 'moyen'),
('Quel est l’objectif des ODD ?', 'atteindre un développement durable', 'Développement durable', 'moyen'),
('Que signifie le terme sobriété énergétique ?', 'réduction de la consommation', 'Développement durable', 'moyen'),
('Pourquoi préserver la biodiversité ?', 'maintenir les équilibres naturels', 'Développement durable', 'moyen'),
('Que signifie le recyclage ?', 'réutilisation des déchets', 'Développement durable', 'facile'),
('Qu’est-ce qu’un produit éco-conçu ?', 'minimise son impact environnemental', 'Développement durable', 'difficile'),
('Pourquoi limiter l’usage du plastique ?', 'réduire la pollution', 'Développement durable', 'facile'),
('Que signifie le terme éco-responsable ?', 'respectueux de l’environnement', 'Développement durable', 'facile'),
('Quel est le rôle des énergies renouvelables ?', 'produire sans épuiser les ressources', 'Développement durable', 'moyen'),

-- Normes et labels
('Que signifie ISO 26000 ?', 'norme RSE', 'Normes et labels', 'moyen'),
('Que signifie le label B Corp ?', 'entreprise à impact positif', 'Normes et labels', 'moyen'),
('Que signifie le label Ecocert ?', 'certification écologique', 'Normes et labels', 'facile'),
('Que signifie le label Fairtrade ?', 'commerce équitable', 'Normes et labels', 'facile'),
('Que signifie le label FSC ?', 'gestion responsable des forêts', 'Normes et labels', 'moyen'),
('Que signifie le label GOTS ?', 'textile biologique', 'Normes et labels', 'facile'),
('Que signifie le label AB ?', 'agriculture biologique', 'Normes et labels', 'facile'),
('Que signifie le label Ecolabel ?', 'produit respectueux de l’environnement', 'Normes et labels', 'moyen'),
('Que signifie le label RSE Lucie ?', 'engagement responsable', 'Normes et labels', 'facile'),
('Que signifie le label ISO 14001 ?', 'management environnemental', 'Normes et labels', 'moyen'),

-- Parties prenantes
('Qui sont les parties prenantes d’une entreprise ?', 'clients, salariés, fournisseurs, société', 'Parties prenantes', 'moyen'),
('Pourquoi consulter les parties prenantes ?', 'intégrer leurs attentes', 'Parties prenantes', 'facile'),
('Que signifie la co-construction ?', 'élaboration collective', 'Parties prenantes', 'moyen'),
('Quel est le rôle des clients dans la RSE ?', 'influencent les pratiques', 'Parties prenantes', 'facile'),
('Pourquoi impliquer les salariés dans la RSE ?', 'favoriser l’adhésion', 'Parties prenantes', 'moyen'),
('Quel est le rôle des ONG ?', 'veille et sensibilisation', 'Parties prenantes', 'moyen'),
('Pourquoi dialoguer avec les collectivités ?', 'favoriser l’ancrage local', 'Parties prenantes', 'moyen'),
('Que signifie la responsabilité sociétale ?', 'impact global de l’entreprise', 'Parties prenantes', 'facile'),
('Quel est le rôle des investisseurs responsables ?', 'financer des projets durables', 'Parties prenantes', 'difficile');