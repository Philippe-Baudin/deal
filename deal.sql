-- phpMyAdmin SQL Dump
-- version 4.9.2
-- https://www.phpmyadmin.net/
--
-- Hôte : 127.0.0.1:3306
-- Généré le :  jeu. 30 jan. 2020 à 15:50
-- Version du serveur :  10.4.10-MariaDB
-- Version de PHP :  7.3.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de données :  `deal`
--

-- --------------------------------------------------------

--
-- Structure de la table `annonce`
--

DROP TABLE IF EXISTS `annonce`;
CREATE TABLE IF NOT EXISTS `annonce` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `titre` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description_courte` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description_longue` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `prix` float NOT NULL,
  `photo` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `pays` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `ville` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `adresse` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `code_postal` char(5) COLLATE utf8mb4_unicode_ci NOT NULL,
  `membre_id` int(11) NOT NULL,
  `categorie_id` int(11) NOT NULL,
  `date_enregistrement` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `membre_id` (`membre_id`),
  KEY `categorie_id` (`categorie_id`)
) ENGINE=InnoDB AUTO_INCREMENT=42 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `annonce`
--

INSERT INTO `annonce` (`id`, `titre`, `description_courte`, `description_longue`, `prix`, `photo`, `pays`, `ville`, `adresse`, `code_postal`, `membre_id`, `categorie_id`, `date_enregistrement`) VALUES
(1, 'Vends maison', 'Maison T5 à vendre à Chéroy (Yonne 89)', 'Venez visiter cette maison située dans un secteur calme avec les commerces, écoles et crèche à pied.\r\nElle se compose d\'une entrée sur pièce à vivre de 44 m² avec pôele à bois et cuisine aménagée ouverte, chambre de plain pied, salle de bains avec baignoire et douche, wc.\r\nA l\'étage : palier, deux belles chambres de 15 m² et 18 m², wc.\r\nAbri voiture et atelier.\r\nDouble vitrage.\r\nTerrasse.\r\nTout à l\'égout.\r\nAucun vis à vis pour ce terrain clos et arboré de 1320 m².\r\nDisponible immédiatement.', 100000, 'img/ref1_9926069611_1.JPG', 'France', 'Chéroy', 'adresse', '89123', 4, 3, '2020-01-19 12:35:48'),
(4, 'Pantalon', 'Pantalon noir taille 38', 'Vends Pantalon noir taille 38, neuf.', 123, 'img/ref4_ref3_pantalon1.jpg', 'France', 'Paris', 'dfsgfdshfsh', '75000', 2, 10, '2020-01-20 11:22:08'),
(6, 'Vends AIXAM Diesel', 'A vendre : voiture sans permis AIXAM Diesel', 'AIXAM Diesel 9 900 €.\r\nCITY SPORT S9\r\nDATE 1ERE MISE EN CIRCULATION 14/10/2019\r\n2 430KMS\r\nAUTORADIO TACTILE 6,2 POUCES+ PORT USB + CAMERA DE RECUL INTEGRÉ\r\nREVISEE ET GARANTIE DANS NOS ATELIERS\r\nFRAIS D\'IMMATRICULATION + KIT DE SECURITE + PLEIN DE GASOIL OFFERTS\r\nPOSSIBILITE DE FINANCEMENT A PARTIR DE 207,94€/MOIS', 9900, 'img/ref0_9926129682_1.jpg', 'France', 'La Ferté-Bernard', 'GARAGE TOUZEAU', '72400', 2, 1, '2020-01-21 12:34:16'),
(9, 'Mercedes Classe E 220 ', 'A vendre : Mercedes Classe E 220 berline bleue', 'Mercedes Classe E 220 BlueTEC Sportline A (9 CV) *, Berline, Diesel, Mars/2016, 43000 Km , 4 portes.\r\nEquipements et options : ABS, Contrôle de pression des pneus, Antipatinage (ASR), Airbag conducteur, Airbag frontaux, Contrôle de stabilité (ESP), Allumage automatique des feux, Rétroviseurs électriques, Filtres à particules (FAP), Banquette AR 1/3 - 2/3, Vitres électriques, Ordinateur de bord, Accoudoir central, Vitres teintées.\r\n', 28500, 'img/ref9_WV158653936_1.jpeg', 'France', 'Four', '10 Rue de Général De Gaulle', '38080', 6, 1, '2020-01-21 12:53:47'),
(10, 'Veste femme', 'Veste femme rouge cuir', 'Veste femme cuir rouge avec capuche 44 oakwood neuve\r\nTaille : 44 (XL)', 55, 'img/ref10_WB163898546_1.png', 'France', 'Nieppe ', 'dfbghf', '59850', 6, 10, '2020-01-21 12:59:30'),
(11, 'PARC ENFANT', 'PARC ENFANT BOIS HETRE. occasion', 'parc enfant pliable marque GEUTHER hauteur 3 niveaux réglables avec roulettes en excellent état.\r\nPuériculture occasion à vendre.', 40, 'img/ref0_WB162416406_1.jpeg', 'France', 'Charbuy ', '...........................', '89113', 4, 11, '2020-01-21 13:09:20'),
(16, 'Gestionnaire ADV (H/F)', 'Mission d\'intérim Gestionnaire ADV (H/F)', 'Lynx RH Paris Ouest, cabinet de recrutement en CDI, CDD, Intérim, recherche pour l’un de ses clients, spécialisé dans la fabrication de produits électroniques, un Gestionnaire ADV (H/F) dans le cadre d\'une mission d\'intérim.\r\n\r\nVos missions :\r\n    Enregistrement de la commande\r\n    Vérification des disponibilités\r\n    Organisation des livraisons\r\n    Suivi des livraisons en contact avec les clients\r\n    Traitement des litiges\r\n    Traitement des retards de règlement\r\n\r\nProfil recherché :\r\nVous bénéficiez d\'une expérience similaire avec un minimum de connaissances dans le domaine de l\'administration des ventes.\r\nVous maîtrisez le Pack Office notamment Excel.\r\nLa maîtrise de SAP est un plus.\r\nVous maîtrisez l\'anglais de manière professionnelle.\r\nVous êtes organisé(e), méthodique et polyvalent(e).\r\n\r\nInformations complémentaires :\r\nType de contrat : Intérim\r\nTemps de travail : Temps plein\r\nSalaire : 27000 € - 31000 € par an', 27000, '', 'France', 'Neuilly', '4 rue Baudin', '92200', 3, 2, '2020-01-24 09:30:56'),
(17, 'Vendeur (H/F)', 'Vendeur (H/F),  Interim Non salarié', 'Nous recherchons pour notre client, magasin spécialisé dans la distribution de produits déco, ameublement, arts de la table, cadeaux,loisirs..., un vendeur H/F.\r\nRattaché au rayon meuble, vous aurez en charge:\r\n- l\'approvisionnement des rayons,\r\n- L\'accueil des clients,\r\n- Le conseil et la vente de produits.\r\n\r\nDe formation dans le commerce, vous possédez une expérience significative dans le secteur de la vente.\r\n\r\nDate de début de contrat : 24/01/2020\r\n\r\n\r\n\r\n\r\nAptitude(s)\r\n\r\n\r\n\r\n\r\n    ACCUEIL PHYSIQUE\r\n\r\n\r\n    APPROVISIONNEMENT RAYON\r\n\r\n\r\n    CONSEIL CLIENT\r\n\r\n\r\n\r\n\r\n\r\nDate de début de contrat : 24/01/2020', 0, '', 'France', 'St Gervais La Foret', '......', '41350', 3, 2, '2020-01-24 09:38:30'),
(18, 'piano numérique', 'A vendre piano numérique de scène', 'A vendre piano numérique de scène\r\n36 sonorités (pianos acoustiques à queue, pianos acoustiques droits, pianos electromécaniques, pianos électriques, clavinettes, jazz orgues, grands orgues, orgues B3, violons, ensembles, ensembles de cordes vintage, choeurs, cuivres doux, cuivres brillants, etc...)\r\nAcheté en décembre 2017, très peu servi, état neuf\r\nVendu avec sa housse à roulettes ', 1100, 'img/ref0_WB163884240_1.jpeg', 'France', 'Alfortville', '......', '94140', 6, 5, '2020-01-24 09:47:43'),
(19, 'CANAPE CONVERTIBLE ROMA', 'CANAPE CONVERTIBLE ROMA occasion, Guyancourt (78280)', 'Vends un canapé convertible avec méridienne\r\nCouleur blanc et noir\r\nA prévoir réparation accoudoir gauche', 100, 'img/ref0_WB164056323_1.jpeg', 'France', 'Guyancourt', '......', '78280', 5, 9, '2020-01-24 09:55:08'),
(20, 'chaussettes ', 'Lot de 3 paires de chaussettes neuves. neuf/revente, Juvisy-sur-Orge (91260)', 'Lot de 3 paires de chaussette neuves avec étiquettes de marque Adriano Stozzi. 75% coton. Jolis motifs.\r\nUne chocolat à pois rouges, une anthracite à pois bleus et une marine rayée en zig zag.\r\nA récupérer sur place. ', 10, 'img/ref20_WB164075604_1.jpeg', 'France', 'Juvisy-sur-Orge', 'Grande Rue', '91260', 5, 10, '2020-01-24 10:09:27'),
(21, 'Château', 'A vendre : Château 13ème siècle, état neuf.', 'Authentique donjon classé Monument Historique des 13ème, 14ème et 15ème siècles, entouré de douves au sein un écrin de tranquillité et de verdure sur 4,5 hectares de parc, bois, et prairies.\r\nVous rejoindrez aisément Château-Gontier et Châteaubriant en 30 minutes, Angers en 1h, Rennes et Laval en 50 min et Paris en 3h30.\r\nL\'ensemble est en parfait état grâce à une restauration de qualité.\r\nSur 4 niveaux, un salon, une salle à manger, une cuisine et 4 belles chambres sont distribués par un superbe escalier à vis en ardoise.\r\nLes terrasses en haut du donjon offre une vue panoramique unique.\r\nLes dépendances viennent encadrer une belle cour d\'honneur.', 595000, 'img/ref21_Capture.JPG', 'France', 'Chateau-Gontier ', '......', '12345', 5, 3, '2020-01-24 10:20:01'),
(22, 'Antiquité', 'Ancien fer à repasser', 'Très ancien fer à repasser, très lourd avec un manche en bois.\r\nA récupérer sur place.', 2, 'img/ref0_WB163870629_1.jpeg', 'France', 'Juvisy-sur-Orge', 'grand\' place', '91260', 5, 11, '2020-01-24 14:25:47'),
(23, 'Hôtel particulier', 'Hôtel particulier 360 m2 12  pièces 4  chambres terrain 1 200 m2 ', 'EN EXCLUSIVITÉ chez CITYA PLANCHON, découvrez cet ensemble architectural d\'exception d\'époque renaissance et XIXème.\r\nLa demeure de 360 m² habitables se trouvant en plein coeur de la ville de CHINON est en bon état général.\r\nElle bénéficie d\'une exposition SUD, et offre des belles pièces de vie lumineuses et quatre chambres dans l\'habitation principale.\r\nLa propriété donne sur un magnifique jardin clos d\'environ 1 200 m² arboré avec un point d\'eau.\r\nEn annexe elle offre un logement d\'amis, des dépendances, une cave et des garages.\r\nVenez découvrir cette demeure dans un lieu unique rare et calme en plein cœur de la ville.', 787000, 'img/ref23_Capture.JPG', 'France', 'Chinon', '20, quai Jeanne d\'Arc', '37500', 6, 3, '2020-01-24 14:33:18'),
(24, 'meubles salle à manger', 'salle à manger en bois vernis plus 8 chaises et table basse occasion', 'Table vernie très bon état 8 chaises tapissées de Nubuk nettoya le très bon etat\r\nTable basse en bois vernis et plaque de verre sur le dessus', 100, 'img/ref24_WB163980649_1.jpeg', 'France', 'Fourqueux ', 'rue de la gare', '78112', 7, 9, '2020-01-24 17:01:00'),
(33, 'Lanterne de camping', 'Lanterne de camping Led - 2W - Vert Cao', 'Lanterne de camping Led - 2W - Vert Cao\r\nTrès Bon Etat\r\nrechargeable. lumière en LED.\r\n', 30.42, 'img/ref33_1248412338_M.jpg', 'France', 'Paris', '... ... ...', '75000', 6, 4, '2020-01-28 12:08:38'),
(34, 'GAMELLE EMAILLE ', 'GAMELLE EMAILLE ', 'GAMELLE EMAILLE ', 49, 'img/ref0_086_12000774_1.jpg', 'France', 'Rouen', 'place du vieux marché', '76000', 6, 4, '2020-01-28 12:13:54'),
(36, 'Tricycle couché ICE Adventure', 'Tricycle couché ICE Adventure 26 : roue arrière de 26', 'En 2010 Trice a remplacé son modèle T par le nouveau Adventure. L\'Adventure en reprend les principale caractéristiques : siège assez haut, conduite relaxante, adapté à tous les terrains.\r\nCadre equipé du &quot;ICE Compact Flat Twist Fold system compact&quot; : le cadre se plie et la roue arrière vient se positionner à plat.\r\nLe comportement de l\'ICE Adventure est très bon. Les vitesses élevées sont facilement contrôlables, la manipulation à basse vitesse est souple, mais le rayon de braquage est assez grand.\r\nLe siège étant plus haut que sur le Sprint ou le VTX, il sera légèrement plus susceptibles de basculer dans les virages serrés rapides. En pratique, cela est rarement un problème pour conducteurs sage.\r\nL\'Adventure a également plus de garde au sol, ce qui peut être agréable sur certains chemins de terre ou pour le franchissement des obstacles urbains comme les ralentisseurs.\r\n', 3015, 'img/ref0_ICE-TRIKE-Adventure-26-2014.jpg', 'France', 'Guérard', '32 bis rue de la gare', '77580', 6, 1, '2020-01-28 12:25:13'),
(37, 'Propriété équestre', 'Maison T8 à vendre à Bergerac', 'Nombre de pièces : 8\r\nSurface : 240m2 (Loi Carrez)\r\n\r\nPropriété équestre étendue sur presque 6 hectares. Longère de 240m2 comprenant une cuisine équipée, un séjour de 52m2 avec cheminée. 5 chambres, 2 salles d\'eau, 1 salle de bain et 2 WC, salle de jeux. Pâtures cloturées, carrière à chevaux de 45 x 30, abris de pré, grange de stockage. Piscine chauffée.\r\nAu calme et à seulement 15 minutes de Bergerac, 5 minutes de toutes commodités et restaurants.\r\n\r\nAgence Eleonor Bergerac à votre disposition pour plus d\'informations sur ce produit.\r\nDisponible immédiatement', 250000, 'img/ref0_9926070132_1.JPG', 'France', 'Bergerac', '... ... ...', '24100', 10, 3, '2020-01-28 14:19:18'),
(38, 'Appartement', 'Magnifique condo au centre-ville de Montréal', 'Magnifique condo à aire ouverte de 18 pieds de plafond avec mezzanine et beaucoup de rangement situé au centre-ville de Montréal et en plein coeur du Quartier des spectacles avec un service de sécurité 24/7. Grande fenestration jusqu\'au plafond. Le condo  dispose d\'un espace de rangement dans l\'édifice et il est possible de réduire le grand salon pour ajouter une 2e chambre.  Vous trouverez tout à proximité (métro Place-des-arts, autobus, supermarché, pharmacie, hôpital CHUM, Cégep du Vieux-Montréal, université McGill et UQAM, garderie, etc.).\r\n- 3 grands ascenseurs ultra rapides\r\n- Chambre disponible pour vos visiteurs\r\n- Espaces communs multiples: Salle de fête/jeu, Lounge pour le piano, Atelier de réparation de vélo, Terrasse avec BBQ et Gym (les deux situés au 28e étage) où vous pouvez apprécier les feux d\'artifice en été\r\n- Belle rue en pavée uni', 445000, 'img/ref0_batisse-condo-montreal-centre-ville-ville-marie-1600-10830052.jpg', 'Quebec', 'Montréal', '506-405 rue de la Concorde, Ville-Marie', '00000', 10, 3, '2020-01-28 14:22:04'),
(39, 'Studio espagne', 'Appartement T1 à vendre à Empuriabrava, Espagne', 'Studio 20 m2, entièrement refait, charge 280€/ an, taxe foncière 250/an.\r\nLe studio est idéalement placé.\r\nLe stationnement est gratuit autour de l immeuble, le studio est face à la piscine, vue sur les Pyrénées.\r\nLe matin vous entendrez les oiseaux.\r\nAu pied de l\'immeuble, un petit café entièrement refait à neuf avec un jeune proprio super sympa. Un réel plus...\r\nLe studio est au deuxième étage. Expo sud, sud est.\r\nEn face de l immeuble, vous avez un supermarché Montserrat, un bazard géant chinois, Lidle, une laverie et la station essence la moins chère du coin. Vous êtes à 5 mn d une plage de rêve, immense. Une trottinette électrique sera idéal pour passer de super vacance. Affaire à saisir rapidement.\r\nSurface : 20m2 environ\r\nEmission de gaz à effet de serre : 5 kgéq/m².an  \r\n Consommation énergétique : 5 kWh/m² an', 38000, 'img/ref0_WI164083109_1.jpeg', 'Espagne', 'Empuriabrava', '... ...', '00000', 16, 3, '2020-01-28 14:25:09'),
(40, 'Porte vélo Siena 2 Fixe', 'Porte vélos universel pour rotule d\'attelage', 'Porte vélos universel type plateforme pour rotule d\'attelage \r\nEquipé d\'un système de fixation rapide et très compact \r\nSangles de sécurité cousues sur le porte vélo \r\nCadre jusqu\'à 60 mm de diamètre \r\nSupports de roues amovibles selon l’espace entre les roues du/des vélos transportés\r\nSupport de plaque complet homologué selon la norme européenne avec feux et prise 13 broches \r\nModèle à assembler \r\nFabriqué en Italie \r\nVerrouillable vélo sur porte-vélo       NON\r\nVerrouillable porte-vélo sur boule d\'attelage   NON\r\nCharge max      34 kg \r\nDimensions (HxLxP)      610 x 1090 x 700/1150 cm', 159, 'img/ref40_porte-velo-siena-2-fixe.jpg', 'France', 'Paris', 'Palais de l\'Elysée, 55 rue du Faubourg-Saint-Honoré', '75008', 8, 4, '2020-01-28 14:28:01'),
(41, 'Bracelets caoutchouc', '10 Bracelets caoutchouc court', 'Ces bracelets courts en caoutchouc de chez Cabanon permettent de fixer le double toit au sol.\r\nCouleur principale : Beige\r\nMatière : Caoutchouc\r\nDimensions : 40 X 48 X 9 mm\r\nGarantie : 2 ans\r\nUtilisation : Equipement Tentes, Fixation', 4, 'img/ref0_bracelets-caoutchouc-court-les-10-cabanon.jpg', 'France', 'Paris', 'Palais de l\'Elysée, 55 rue du Faubourg-Saint-Honoré', '75008', 8, 4, '2020-01-28 14:29:50');

-- --------------------------------------------------------

--
-- Structure de la table `categorie`
--

DROP TABLE IF EXISTS `categorie`;
CREATE TABLE IF NOT EXISTS `categorie` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `titre` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `mots_cles` text COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=14 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `categorie`
--

INSERT INTO `categorie` (`id`, `titre`, `mots_cles`) VALUES
(1, 'Véhicule', 'Voitures, Motos, Bateaux, Vélos, Equipement'),
(2, 'Emploi', 'Offres d\'emploi'),
(3, 'Immobilier', 'Ventes, Locations, Colocations, Bureaux, Logement'),
(4, 'Vacances', 'Camping, Hôtels, Hôte'),
(5, 'Multimédia', 'Jeux vidéos, Informatique, Image, Son, Téléphone'),
(6, 'Loisirs', 'Films, Musique, Livres'),
(7, 'Matériel', 'Outillage, Fournitures de bureau, Matériel agricole'),
(8, 'Service', 'Prestation de services, Evénements'),
(9, 'Maison', 'Ameublement, Electroménager, Bricolage, Jardinage'),
(10, 'Vêtements', 'Jean, Chemise, Robe, Chaussures'),
(11, 'Autres', '');

-- --------------------------------------------------------

--
-- Structure de la table `commentaire`
--

DROP TABLE IF EXISTS `commentaire`;
CREATE TABLE IF NOT EXISTS `commentaire` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `commentaire` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `membre_id` int(11) NOT NULL,
  `annonce_id` int(11) NOT NULL,
  `date_enregistrement` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `annonce_id` (`annonce_id`),
  KEY `membre_id` (`membre_id`)
) ENGINE=InnoDB AUTO_INCREMENT=26 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `commentaire`
--

INSERT INTO `commentaire` (`id`, `commentaire`, `membre_id`, `annonce_id`, `date_enregistrement`) VALUES
(20, 'Ca a l\'air d\'une maison sympa ...', 2, 1, '2020-01-23 16:43:26'),
(21, 'Est-ce qu\'il y a des caves ?', 3, 21, '2020-01-24 16:15:48'),
(22, '@ John Snow :\r\nOui. C\'est la partie la plus ancienne du château : des caves voutées du 13ème siècle.', 5, 21, '2020-01-30 15:58:13'),
(23, 'Et des oubliettes, des passages secrets ?', 7, 21, '2020-01-30 15:59:10'),
(24, '@ pirate :\r\nJ\'ai bien peur que non.', 5, 21, '2020-01-30 16:00:00'),
(25, 'On peut visiter ?', 6, 21, '2020-01-30 16:00:30');

-- --------------------------------------------------------

--
-- Structure de la table `membre`
--

DROP TABLE IF EXISTS `membre`;
CREATE TABLE IF NOT EXISTS `membre` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `pseudo` varchar(45) COLLATE utf8mb4_unicode_ci NOT NULL,
  `mdp` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `nom` varchar(45) COLLATE utf8mb4_unicode_ci NOT NULL,
  `prenom` varchar(45) COLLATE utf8mb4_unicode_ci NOT NULL,
  `telephone` varchar(45) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(45) COLLATE utf8mb4_unicode_ci NOT NULL,
  `civilite` enum('M.','Mme') COLLATE utf8mb4_unicode_ci NOT NULL,
  `role` enum('user','admin') COLLATE utf8mb4_unicode_ci NOT NULL,
  `date_enregistrement` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=21 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `membre`
--

INSERT INTO `membre` (`id`, `pseudo`, `mdp`, `nom`, `prenom`, `telephone`, `email`, `civilite`, `role`, `date_enregistrement`) VALUES
(2, 'machin', '$2y$10$IxLpSOpGx6ZQqxArIYC8ZO.FXCbRkR5/GM6reh7xMn9ml.iQEZdVC', 'Truc', 'Machin', '0123456789', 'machin@webmail.com', 'M.', 'admin', '2020-01-28 14:36:20'),
(3, 'John Snow', '$2y$10$UczcoTJLv/L7LySaepS2cOjkgl8pZaCBUv/TkFTkiw.8IZ3arAreu', 'Targarien', 'Aegon', '0147258369', 'aegon-targarien@gameofthrone.com', 'M.', 'user', '2020-01-28 14:36:29'),
(4, 'junior', '$2y$10$Hb7Gu3fi9WoJySUXo9sAxebapHAGPrlfRRiXNFzJGRe80XBq9QiXe', 'Jones', 'Indiana', '0654987321', 'contact@www.IndianaJones.com', 'M.', 'user', '2020-01-28 14:36:38'),
(5, 'philippe', '$2y$10$n6jYPWBAflGVVQJaFfTKzOVSTGeCL4uV20UunUPvG//stZmWH.TXW', 'BAUDIN', 'Philippe', '0175815597', 'philippe-baudin52@sfr.fr', 'M.', 'admin', '2020-01-27 15:52:38'),
(6, 'jeanne', '$2y$10$VspjMNRDyCg0PdIbguj/oeRKk4capaLzIoZch2EM8BBLXxM9LuAAO', 'd\'Arc', 'Jeanne', '0000000000', 'JeanneDArc@gmail.com', 'Mme', 'user', '2020-01-28 14:36:57'),
(7, 'pirate', '$2y$10$mJ/ivDqF6Lf/GeCwSefLO.JXWmhD7PeDGScWEBRzaery19WMdKuQ6', 'Doe', 'John', '9638527410', 'john.doe@yahoo.com', 'M.', 'user', '2020-01-28 14:37:07'),
(8, 'président', '$2y$10$c9TU3WQdOoRNEBCV9hh.9O0tr.1lMa3j8UF/XtAvMaeHGneInhVu2', 'Macron', 'Emmanuel', '0123456789', 'emanuel.macron@gouv.fr', 'M.', 'user', '2020-01-28 14:37:18'),
(9, 'babette', '$2y$10$b.khq3cPu9J6CEKYoFWFOOjJGI34DeBDZ7Qodd8FxML0iqjkS.vwa', 'LABEAU', 'Elisabeth', '0123456789', 'elabeau@gmail.com', 'Mme', 'user', '2020-01-28 14:38:08'),
(10, 'Ahmed-One', '$2y$10$vjC/Gt9t4zKHd7oDrcVWkebQiCCgxQeXdna.noe4F136LfuOUb/3u', 'Ahmed', 'Youssouf', '0123456789', 'ayoussouf@gmail.com', 'M.', 'user', '2020-01-28 14:37:38'),
(11, 'Ahmed-Two', '$2y$10$UtEr1Wy932JwgjHRuXM2K..bOeGjHDx4cnU0/i/r.BJ48G3fuQYS.', 'Ahmed', 'Tou', '0123456789', 'tahmed@gmail.com', 'M.', 'user', '2020-01-28 14:37:48'),
(12, 'Vincent', '$2y$10$yOj48qfZKC9A5t9OfIn0wOQuSGuzHOtfoU/hI2DrXYJgzuQkG2Soe', 'Wong', 'Vincent', '0123456789', 'vwong@gmail.com', 'M.', 'user', '2020-01-28 14:38:19'),
(13, 'Nico', '$2y$10$9Qu40xuoLcbS.7ACYqukqumvlT3k2PBoTTD85daBxF2sJMRdwck7i', 'PERIGOIS', 'Nicolas', '0123456789', 'nperigois@gmail.com', 'M.', 'user', '2020-01-28 14:38:30'),
(14, 'saida', '$2y$10$wU.EdLFGkO61S.6gY84X8udToA6Vt5GB7CvqNKZ1dDp/nvT6HSGYG', 'OUMAZIGH', 'Saida', '0123456789', 'soumazigh@gmail.com', 'Mme', 'user', '2020-01-28 14:38:47'),
(15, 'Arnaud', '$2y$10$R4V5q8gTptgim9uTpGgjCOkepConq2bDrexLy7JbxvfyXtt7pDDgy', 'MALFAIT', 'Arnaud', '0123456789', 'amalfait@gmail.com', 'M.', 'user', '2020-01-28 14:39:20'),
(16, 'Solène', '$2y$10$8DHJ4Obg.omp4oC76ilXweqQrs3lhgVVynRJv/OJyuc8tnuLoZk2W', 'Le Den', 'Solène', '0123456789', 'sleden@gmail.com', 'Mme', 'user', '2020-01-28 14:39:33'),
(17, 'Laurent', '$2y$10$SmCEHut0lZU0fgmkgvb3Xezq6RBHaXn9kwrhoLIR5hPWoNDAJwoPC', 'CHAPPUIS', 'Laurent', '0123456789', 'lchappuis@gmail.com', 'M.', 'user', '2020-01-28 14:40:27'),
(18, 'christophe', '$2y$10$R5B4I3ygaF0NV2nZGTDdc.PK7npCPoZDI1nbAZIZdemFAh3bgoLbS', 'JOSSE', 'Christophe', '0123456789', 'cjosse@gmail.com', 'M.', 'user', '2020-01-28 14:40:16'),
(19, 'benjamin', '$2y$10$ZUXD4e4IRUrdX/Y3w66lOetEPO9tGODyyczwCwUht98s8e1REyjBa', 'Benjamin', 'ESTEVES', '0123456789', 'besteve@gmail.com', 'M.', 'user', '2020-01-28 14:40:06'),
(20, 'aurélien', '$2y$10$NftNDcf4pNBQpfAwHXMAOu/f2yOL9X/RqR8kedwlDnzR.s2pMU.16', 'BELINGARD', 'Aurélien', '0123456789', 'abelingard@gmail.com', 'M.', 'user', '2020-01-28 14:39:57');

-- --------------------------------------------------------

--
-- Structure de la table `note`
--

DROP TABLE IF EXISTS `note`;
CREATE TABLE IF NOT EXISTS `note` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `note` int(11) NOT NULL,
  `avis` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `membre_id1` int(11) NOT NULL,
  `membre_id2` int(11) NOT NULL,
  `date_enregistrement` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `membre_id1` (`membre_id1`),
  KEY `membre_id2` (`membre_id2`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `note`
--

INSERT INTO `note` (`id`, `note`, `avis`, `membre_id1`, `membre_id2`, `date_enregistrement`) VALUES
(1, 3, 'RAS', 2, 3, '2020-01-20 09:45:24'),
(2, 3, 'RAS', 2, 4, '2020-01-20 09:45:24'),
(3, 2, 'RAS', 2, 5, '2020-01-20 09:45:24'),
(4, 4, 'Sympa', 3, 2, '2020-01-20 09:45:24'),
(5, 1, 'Utilise le copier collé pour faire ses annonces', 3, 4, '2020-01-20 09:45:24'),
(6, 3, 'RAS', 2, 5, '2020-01-20 09:45:24'),
(7, 3, 'RAS', 5, 2, '2020-01-20 09:45:24'),
(8, 4, 'RAS', 5, 3, '2020-01-20 09:45:24'),
(10, 4, 'Merci de nous faire rêver ...', 7, 6, '2020-01-24 16:41:44');

-- --------------------------------------------------------

--
-- Structure de la table `photo`
--

DROP TABLE IF EXISTS `photo`;
CREATE TABLE IF NOT EXISTS `photo` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `photo` varchar(200) COLLATE utf8mb4_unicode_ci NOT NULL,
  `annonce_id` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `annonce_id` (`annonce_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Contraintes pour les tables déchargées
--

--
-- Contraintes pour la table `annonce`
--
ALTER TABLE `annonce`
  ADD CONSTRAINT `annonce_ibfk_1` FOREIGN KEY (`membre_id`) REFERENCES `membre` (`id`) ON DELETE NO ACTION ON UPDATE CASCADE,
  ADD CONSTRAINT `annonce_ibfk_2` FOREIGN KEY (`categorie_id`) REFERENCES `categorie` (`id`) ON DELETE NO ACTION ON UPDATE CASCADE;

--
-- Contraintes pour la table `commentaire`
--
ALTER TABLE `commentaire`
  ADD CONSTRAINT `commentaire_ibfk_1` FOREIGN KEY (`annonce_id`) REFERENCES `annonce` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `commentaire_ibfk_2` FOREIGN KEY (`membre_id`) REFERENCES `membre` (`id`) ON DELETE NO ACTION ON UPDATE CASCADE;

--
-- Contraintes pour la table `note`
--
ALTER TABLE `note`
  ADD CONSTRAINT `note_ibfk_1` FOREIGN KEY (`membre_id1`) REFERENCES `membre` (`id`) ON DELETE NO ACTION ON UPDATE CASCADE,
  ADD CONSTRAINT `note_ibfk_2` FOREIGN KEY (`membre_id2`) REFERENCES `membre` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Contraintes pour la table `photo`
--
ALTER TABLE `photo`
  ADD CONSTRAINT `photo_ibfk_1` FOREIGN KEY (`annonce_id`) REFERENCES `annonce` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
