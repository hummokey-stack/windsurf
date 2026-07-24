import { sqliteTable, text, integer, real } from 'drizzle-orm/sqlite-core';

export const settings = sqliteTable('settings', {
  id: integer('id').primaryKey({ autoIncrement: true }),
  logo: text('logo').default('assets/images/logo.png'),
  siteName: text('site_name').default('GlisseShop'),
  slogan: text('slogan').default('Votre spécialiste wingsurf & sports de glisse'),
  email: text('email'),
  telephone: text('telephone'),
  adresse: text('adresse'),
  horaires: text('horaires'),
  livraisonGratuiteMontant: real('livraison_gratuite_montant').default(100.00),
  facebook: text('facebook'),
  instagram: text('instagram'),
  metaDescription: text('meta_description'),
  whatsapp: text('whatsapp'),
  tiktok: text('tiktok'),
  favicon: text('favicon'),
  ogTitle: text('og_title'),
  ogDescription: text('og_description'),
  ogImage: text('og_image'),
  ogUrl: text('og_url'),
  ogType: text('og_type').default('website'),
});

export const categories = sqliteTable('categories', {
  id: integer('id').primaryKey({ autoIncrement: true }),
  nom: text('nom').notNull(),
  slug: text('slug').notNull().unique(),
  parentId: integer('parent_id'),
  description: text('description'),
  image: text('image'),
  ordre: integer('ordre').default(0),
  actif: integer('actif').default(1),
});

export const produits = sqliteTable('produits', {
  id: integer('id').primaryKey({ autoIncrement: true }),
  nom: text('nom').notNull(),
  slug: text('slug').notNull().unique(),
  categorieId: integer('categorie_id').references(() => categories.id),
  marque: text('marque'),
  prix: real('prix').notNull(),
  ancienPrix: real('ancien_prix'),
  description: text('description'),
  descriptionCourte: text('description_courte'),
  stock: integer('stock').default(0),
  imagePrincipale: text('image_principale'),
  imagesSecondaires: text('images_secondaires'), // JSON string array
  variantes: text('variantes'),                 // JSON string array
  niveau: text('niveau'),
  estOccasion: integer('est_occasion').default(0),
  estNouveaute: integer('est_nouveaute').default(0),
  estPromo: integer('est_promo').default(0),
  estActif: integer('est_actif').default(1),
  dateCreation: text('date_creation').default('CURRENT_TIMESTAMP'),
  type: text('type').default('simple'),
  attributs: text('attributs'),
});

export const produitVariations = sqliteTable('produit_variations', {
  id: integer('id').primaryKey({ autoIncrement: true }),
  produitId: integer('produit_id').notNull().references(() => produits.id, { onDelete: 'cascade' }),
  attributs: text('attributs').notNull(),
  prix: real('prix').notNull(),
  ancienPrix: real('ancien_prix'),
  stock: integer('stock').default(0),
  reference: text('reference'),
});

export const commandes = sqliteTable('commandes', {
  id: integer('id').primaryKey({ autoIncrement: true }),
  reference: text('reference').unique(),
  clientNom: text('client_nom'),
  clientEmail: text('client_email'),
  clientTelephone: text('client_telephone'),
  adresseLivraison: text('adresse_livraison'),
  articles: text('articles'), // JSON string
  sousTotal: real('sous_total'),
  fraisLivraison: real('frais_livraison'),
  total: real('total'),
  statut: text('statut').default('en_attente'),
  dateCommande: text('date_commande').default('CURRENT_TIMESTAMP'),
});

export const blog = sqliteTable('blog', {
  id: integer('id').primaryKey({ autoIncrement: true }),
  titre: text('titre').notNull(),
  slug: text('slug').unique(),
  contenu: text('contenu'),
  extrait: text('extrait'),
  image: text('image'),
  categorie: text('categorie'),
  actif: integer('actif').default(1),
  dateCreation: text('date_creation').default('CURRENT_TIMESTAMP'),
});

export const admin = sqliteTable('admin', {
  id: integer('id').primaryKey(),
  login: text('login').default('admin'),
  password: text('password'),
});

export const newsletter = sqliteTable('newsletter', {
  id: integer('id').primaryKey({ autoIncrement: true }),
  email: text('email').notNull().unique(),
  statut: text('statut').default('actif'),
  dateInscription: text('date_inscription').default('CURRENT_TIMESTAMP'),
});
