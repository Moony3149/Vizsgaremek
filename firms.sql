-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Gép: 127.0.0.1
-- Létrehozás ideje: 2026. Feb 15. 16:34
-- Kiszolgáló verziója: 10.4.32-MariaDB
-- PHP verzió: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Adatbázis: `firms`
--

-- --------------------------------------------------------

--
-- Tábla szerkezet ehhez a táblához `favorites`
--

CREATE TABLE `favorites` (
  `user_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- A tábla adatainak kiíratása `favorites`
--

INSERT INTO `favorites` (`user_id`, `product_id`) VALUES
(4, 1),
(4, 2),
(4, 3);

-- --------------------------------------------------------

--
-- Tábla szerkezet ehhez a táblához `firm`
--

CREATE TABLE `firm` (
  `ID` int(11) NOT NULL,
  `company_reg_number` varchar(10) DEFAULT NULL,
  `brand_name` varchar(50) NOT NULL,
  `worker_name` varchar(50) DEFAULT '',
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `approved` tinyint(1) DEFAULT 0,
  `profile_pic` varchar(255) DEFAULT 'default_firm.png'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- A tábla adatainak kiíratása `firm`
--

INSERT INTO `firm` (`ID`, `company_reg_number`, `brand_name`, `worker_name`, `email`, `password`, `approved`, `profile_pic`) VALUES
(1, '123456789', 'HARIBO', '', 'haribo.og@gmail.com', '$2y$10$vNU0lh7ZY0AhpY9JBIISVerfn83r5jLd7c16mlmB6IKm51F2OtKEe', 1, 'default_firm.png'),
(2, '1310040628', 'TESCO GLOBAL Zrt.', 'Farkas Fanni', 'ffanni@gmail.com', '$2y$10$g7DEbEpI0sVHXuGRl6FzBejjojE3waqtHQn0PTFq1jHyrohnDLXPa', 1, 'default_firm.png');

-- --------------------------------------------------------

--
-- Tábla szerkezet ehhez a táblához `notifications`
--

CREATE TABLE `notifications` (
  `id` int(11) NOT NULL,
  `firm_id` int(11) NOT NULL,
  `message` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `is_read` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Tábla szerkezet ehhez a táblához `password_resets`
--

CREATE TABLE `password_resets` (
  `id` int(11) NOT NULL,
  `email` varchar(255) NOT NULL,
  `token` varchar(255) NOT NULL,
  `expires_at` datetime NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- A tábla adatainak kiíratása `password_resets`
--

INSERT INTO `password_resets` (`id`, `email`, `token`, `expires_at`, `created_at`) VALUES
(1, 'elsovas@hotmail.com', '9ede4119c09d18057a5bf7093af329558808c8c7afb6ceee851db4a6d70226ee', '2026-02-05 20:20:26', '2026-02-05 18:50:26'),
(2, 'elsovas@hotmail.com', '36eef65e4adeef88338561321208de2f15b58066467c843c1a5f1ecfce1b28b7', '2026-02-05 20:24:28', '2026-02-05 18:54:28');

-- --------------------------------------------------------

--
-- Tábla szerkezet ehhez a táblához `products`
--

CREATE TABLE `products` (
  `ID` int(11) NOT NULL,
  `name` varchar(50) NOT NULL,
  `description` varchar(255) NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `capacity` int(8) NOT NULL,
  `amount` int(11) NOT NULL,
  `active` tinyint(1) NOT NULL,
  `type` enum('Zöldség és gyümölcs','Tejtermék- tojás','Pékáru','Húsáru','Mélyhűtött','Alapvető élelmiszerek','Italok','Speciális','Háztartás','Drogéria','Kisállat','Otthon-hobbi') NOT NULL,
  `picture` varchar(255) DEFAULT NULL,
  `firm_id` int(11) DEFAULT NULL,
  `approved` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- A tábla adatainak kiíratása `products`
--

INSERT INTO `products` (`ID`, `name`, `description`, `price`, `capacity`, `amount`, `active`, `type`, `picture`, `firm_id`, `approved`) VALUES
(1, 'Haribo Goldbären gyümölcsízű gumicukorka 57g', 'Glükózszirup; cukor; zselatin; szőlőcukor; gyümölcslé gyümölcslé-koncentrátumból (alma, eper, málna, narancs, citrom, ananász); étkezési sav (citromsav); gyümölcs- és növényi sűrítmények (sáfrányos szeklice, spirulina, alma, bodzabogyó, narancs, fekete ri', 299.00, 56, 3432, 1, 'Alapvető élelmiszerek', 'haribo_pocket_size.jpg', 1, 1),
(2, 'Haribo Goldbären gyümölcsízű gumicukorka 100g', 'Glükózszirup; cukor; zselatin; szőlőcukor; gyümölcslé gyümölcslé-koncentrátumból (alma, eper, málna, narancs, citrom, ananász); étkezési sav: citromsav; gyümölcs- és növényi sűrítmények: sáfrányos szeklice, spirulina, alma, bodzabogyó, narancs, fekete rib', 499.00, 100, 4515, 1, 'Alapvető élelmiszerek', 'haribo_100g.jpg', 1, 1),
(3, 'Haribo Starmix', 'Glükózszirup; cukor; zselatin; szőlőcukor; étkezési sav: citromsav; gyümölcslé gyümölcslé-koncentrátumból (alma, eper, málna, narancs, citrom, ananász); gyümölcs- és növényi sűrítmények (sáfrányos szeklice, spirulina, alma, bodzabogyó, narancs, fekete rib', 890.00, 250, 3, 1, 'Alapvető élelmiszerek', '1770800504_1770198067_Haribo-Starmix-Minis-250g.jpg', 1, 1),
(4, 'Haribo Rainbow Frogs ', 'Glükózszirup; cukor; zselatin; étkezési savak (citromsav, almasav); gyümölcs- és növényi sűrítmények (sáfrányos szeklice, spirulina, alma, sárgarépa, fekete ribizli, bodzabogyó, citrom, kivi, hibiszkusz, szőlő, arónia); aroma; fényezőanyag (karnaubaviasz)', 499.00, 125, 32, 1, 'Alapvető élelmiszerek', '1770800526_1770198732_Harbio_frogs.jpg', 1, 1),
(5, 'HARIBO Tropic Party Size', 'Glükózszirup; cukor; zselatin; szőlőcukor; étkezési sav: citromsav; gyümölcs- és növényi sűrítmények (sáfrányos szeklice, alma, spirulina, sárgarépa, bodzabogyó, citrom, fekete ribizli, kivi, hibiszkusz, szőlő, arónia, mangó, passiógyümölcs); aroma; fénye', 1800.00, 1000, 4354, 1, 'Alapvető élelmiszerek', '1770806267_haribo-tropusi-gumicukor-1000g.jpg', 1, 1),
(6, 'Saga Forest Fruit ízesített gyümölcstea 20 filter ', 'Összetevők\r\nHibiszkusz, Csipkebogyó, Alma, Aroma, Édesgyökér, Máina (2%), Áfonya (1%), Bodzabogyó (1%), Fekete berkenye (1%). 1 teafiltert 200 ml frissen felforrt vízben 4-5 percig áztasson (tej és cukor nélkül).', 699.00, 0, 200, 1, 'Italok', '1771154422_saga_forest_fruit_tea.jpg', 2, 1),
(7, 'Karaván szemes kávé 1000 g', 'Karaván szemes kávé 1000 g;Főbb jellemzők: Izgalmas ízvilág, Középbarna pörkölés, Intenzív íz. Szemes pörkölt kávé. Izgalmas ízvilágú kávé, mely középbarna pörkölésével és intenzív ízével minden alkalommal képes új arcát mutatni. Ez a karakteresen keserű ', 7190.00, 1000, 36, 1, 'Italok', 'karavan_szemes_kave_1000.jpg', 2, 1),
(8, 'Nesquik instant cukrozott kakaóitalpor vitaminokka', 'Főbb jellemzők: Gluténmentes termék. Cukor, Zsírszegény kakaópor* 23%, Emulgeálószer (szójalecitin), Étkezési só, Vitaminok [nátrium-L-aszkorbát (C-vitamin), kolekalciferol (D-vitamin)], Természetes aroma, Fahéj, *Rainforest Alliance tanúsított. További i', 899.00, 150, 30, 1, 'Italok', 'nesquik_instant_cukrozott-kakatalpor_vitaminokkal_150.jpg', 2, 1),
(9, 'Mizse szénsavmentes természetes ásványvíz 1,5 l', 'Főbb jellemzők: 4x Magyar Brands \'20 \'21 \'22 \'23. Szénsavmentes természetes ásványvíz. Vastalanítva. Alkalmas nátrium-szegény diétához. Lúgos kémhatású termék. Csomagolás mérete: 1.5l. Külső : Nem újrahasználható kötelezően visszaváltási díjas termék, Möb', 159.00, 2, 45, 1, 'Italok', 'mizse_asvanyviz_1_5l_szensavmentes.jpg', 2, 1),
(10, 'Tesco 100% almalé 1 l', 'Sűrítményből készült almalé. Pasztőrözött.\r\nCsomagolás mérete: 1l Sűrítményből készült almalé, Gyümölcstartalom: 100%.', 469.00, 1, 160, 1, 'Italok', '1771154434_tesco_100_almale.jpg', 2, 1),
(11, 'Tesco citrom ízesítő 40% citromlé tartalommal 1,5 ', 'Ivóvíz, Citromlé (40%) (citromlé sűrítményből), Étkezési sav (citromsav), Aroma, Tartósítószer (kálium-szorbát), Stabilizátorok (nátrium-karboxi-metil-cellulóz, gumiarábikum, fagyanta glicerinészterei), Színezék (karotinok), Antioxidánsok (kálium-metabisz', 349.00, 2, 80, 1, 'Italok', 'ts_citrom_izesito1_5.jpg', 2, 1),
(12, 'Coca-Cola colaízű szénsavas üdítőital 1,75 l', 'Víz, Fruktóz-glükózszirup, Szén-dioxid, Színezék: szulfitos-ammóniás karamell, Étkezési sav: foszforsav, Természetes aromák, Koffein aroma.', 759.00, 2, 88, 1, 'Italok', 'coca_cola_1_75.jpg', 2, 1),
(13, 'Fuze Tea szénsavmentes eper-aloe vera-ízű üdítőita', 'Víz, Cukor, Fruktóz, Savanyúságot szabályozó anyagok: almasav és nátrium-citrátok, Aromák, Zöld tea kivonat** (0, 12%), Eperlé sűrítményből (0, 1%), Antioxidáns: aszkorbinsav, Édesítőszer: sztéviából származó szteviol-glikozidok, **Rainforest Alliance tan', 599.00, 2, 60, 1, 'Italok', 'fuze_tea_green_eper_aloe_1_5.jpg', 2, 1),
(14, 'Monster Energy szénsavas energiaital koffeinnel éd', 'Víz, Cukor, Glükózszirup, Szén-dioxid, Étkezési sav (citromsav), Aromák, Savanyúságot szabályozó anyag (nátrium-citrátok), L-karnitin-L-tartarát (0, 04%), Tartósítószerek (szorbinsav, benzoesav), Koffein (0, 03%), Színezék (antocianinok), Vitaminok (niaci', 599.00, 1, 200, 1, 'Italok', 'monster.jpg', 2, 1),
(15, 'Soproni Meggy Ale szűretlen felsőerjesztésű sörkül', 'Víz, Árpamaláta, Fruktóz, Meggylé koncentrátumból (7, 25%), Almalé koncentrátumból (1%), Savanyúságot szabályzó anyag: citromsav, Természetes színezék, Komlókivonat, Élesztő. Meggyes sörkülönlegesség\r\nFenntarthatósági jelentés: heinekenhungaria.hu', 459.00, 1, 200, 1, 'Italok', '1771154456_soproni_meggy_ale.jpg', 2, 1),
(16, 'Mizo sovány, laktózmentes kakaó édesítőszerekkel 4', 'Fölözött tej, Zsírszegény kakaópor (1, 4%), Édesítőszerek (ciklamátok, szacharinok), Aroma, Sűrítőanyagok (cellulóz, cellulózgumi), Savanyúságot szabályozó anyag (nátrium-foszfátok), Laktáz enzim, 0% zsírtartalom', 489.00, 0, 30, 1, 'Speciális', 'mizo_laktozmentes_edesitovel.jpg', 2, 1),
(17, 'Foody Free hummus chips céklával 50 g', 'Csicseriborsóliszt (25%), Rizsliszt, Kukoricaliszt, Finomított napraforgó étolaj, Céklapor (2, 7%), Étkezési só', 329.00, 50, 45, 1, 'Speciális', 'foody_free_hummus_chips.jpg', 2, 1),
(18, 'Biopont bio gluténmentes földimogyorós kölesgolyó ', 'Hántolt köles*, Pörkölt földimogyoró* (18%), Napraforgó étolaj*, Tengeri só, *= Ellenőrzött ökológiai gazdálkodásból származó összetevő.', 579.00, 75, 45, 1, 'Speciális', 'biopont_glutenm_mogyor_kolesgoly.jpg', 2, 1),
(19, 'Gullón élelmi rostban gazdag keksz, édesítőszerrel', 'Teljes kiőrlésű búzaliszt 58%, Édesítőszer (maltit), Növényi olaj (magas olajsavtartalmú napraforgó olaj) 16%, Borsórost 4%, Inulin, Oligofruktóz, Térfogatnövelő szerek (nátrium-hidrogén-karbonát, ammónium-hidrogén-karbonát), Emulgeálószer (szójalecitin),', 659.00, 170, 50, 1, 'Speciális', 'Gullon_keksz_cukorm.jpg', 2, 1),
(20, 'Pölöskei Diab mangó-maracuja ízű szörp édesítőszer', 'Pölöskei Aquafitt természetes ásványvíz, Étkezési sav (citromsav), Mangó és maracuja készítmény (stabilizátor: gumiarábikum, fagyanta glicerin észterei, aromák, répa koncentrátum, színezék: karotinok), Édesítőszerek (nátrium-ciklamát, aceszulfám-K, szukra', 719.00, 1, 40, 1, 'Speciális', 'poloskei_diab_mango_marac_szorp_edesitovel.jpg', 2, 1),
(21, 'Fino VegaJó Vegaföl növényi alapú készítmény 150 g', 'Ivóvíz, Kókuszzsír 20%, Dextróz, Keményítő, Babfehérje, Trikalcium-foszfát, Inaktív élesztő, Tengeri só, Baktérium-színtenyészet, Zsírtartalom: 20% (m/m).', 379.00, 150, 23, 1, 'Speciális', 'fino_vegajo_vegafol_150g.jpg', 2, 1),
(22, 'Győri Korpovit ropogós keksz teljes kiőrlésű gabon', 'gabonák 92, 3 % (teljes kiőrlésű BÚZALISZT 74, 7 %, BÚZALISZT 17, 6 %), pálmaolaj, ÁRPAMALÁTA kivonat, BÚZAKORPA 1, 5 %, térfogatnövelő szerek (ammónium-karbonátok, nátrium-karbonátok), élesztő, étkezési só, lisztkezelőszer (nátrium-METABISZULFIT).TARTALM', 459.00, 174, 45, 1, 'Speciális', 'gyori_korpovit_teljes_olkesu_keksz.jpg', 2, 1),
(23, 'Alpro zsírszegény cukormentes mandulaital hozzáado', 'Ivóvíz, Mandula (2, 3%), Kalcium (trikalcium-foszfát), Tengeri só, Stabilizátorok (szentjánoskenyérliszt, gellángumi), Emulgeálószer (lecitinek), B2-vitamin, B12-vitamin, E-vitamin, D2-vitamin', 1099.00, 1, 40, 1, 'Speciális', 'alpro_cukorm_mandulaital.jpg', 2, 1),
(24, 'Zott Protein kakaós puding édesítőszerekkel 200 g', 'Tejfehérje, Teljes tej, Tejszín, Zsírszegény kakaópor (2%), Módosított keményítő, Keményítő, Kakaópor (0, 4%), Sűrítőanyagok: karragén, guargumi, Savanyúságot szabályozó anyagok: nátrium-foszfátok, difoszfátok, trifoszfátok, polifoszfátok, Édesítőszerek: ', 489.00, 200, 45, 1, 'Speciális', 'zott_protein_kakaos_puding_edesitett.jpg', 2, 1),
(25, 'Tesco Plant Chef csicseriborsóból és szezámmagból ', 'Főtt csicseriborsó (53%) (csicseriborsó, ivóvíz), Ivóvíz, Repceolaj, Tahini szezámmagpép (10%), Ételecet, Étkezési só, Fűszerek, Savanyúságot szabályozó anyag (citromsav).', 539.00, 200, 45, 1, 'Speciális', 'ts_plant_chef_csicseri_szezam_krem.jpg', 2, 1),
(26, 'Persil Color mosógél 33 mosás 1,485 l', '5-15% anionos felületaktív anyagok, nemionos felületaktív anyagok, <5% szappan, Enzimek, Illatanyagok (Terpineol, Limonene, Turpentine, Pinene, Citrus Aurantium Peel Oil, Pogostemon Cablin Oil), Benzisothiazolinone.', 4079.00, 1, 30, 1, 'Háztartás', 'persil_color_mosogel_33_mosas.jpg', 1, 1),
(27, 'Coccolino Sensitive & Soft öblítőkoncentrátum 68 m', '5-15%: kationos felületaktív anyagok, <5%: illatanyag, Lactic Acid, Sodium Benzoate.', 1759.00, 2, 45, 1, 'Háztartás', 'coccolino_sens_soft_oblito_68_mosas.jpg', 1, 1),
(28, 'Jar Lemon Folyékony Mosogatószer. Zsíroldó Áztatás', '5-15% Anionos Felületaktív Anyagok, Nemionos Felületaktív Anyagok, Tartósítószerek, Benzisothiazolinone, Phenoxyethanol, Illatanyagok, Linalool.', 1499.00, 1, 45, 1, 'Háztartás', 'jar_lemon_mosogatoszer_900ml.jpg', 1, 1),
(29, 'DOMESTOS Extended Power fertőtlenítő hatású folyék', 'Fertőtlenítőszer: nátrium-hipoklorit 4, 5 g / 100 g, <5% klór alapú fehérítőszer/fertőtlenítőszer (nátrium-hipoklorit), nem ionos felületaktív anyagok, szappan, illatanyag.', 769.00, 1, 40, 1, 'Háztartás', 'domestos_eextended_folyekony_tisztito_pine.jpg', 1, 1),
(30, '4MAX mosogatószivacs 10 db', 'Hatékony dörzsréteggel.\r\nKülső cégek logói: Möbius-szalag', 239.00, 10, 30, 1, 'Háztartás', '4max_mosogatoszivacs_10.jpg', 1, 1),
(31, 'Tesco sütőpapír 38 x 42 cm 20 ív', 'Nincs szükség zsiradékra, nem ragad, mindkét oldala használható, 220°C-ig hőálló, mikrohullámú sütőbe is helyezhető.', 525.00, 20, 50, 1, 'Háztartás', 'ts_sutopapir_20.jpg', 1, 1),
(32, '4MAX szemeteszsák 60 l 26 db', 'Csomag: Újrahasznosítható. Származási hely: Vietnam', 529.00, 26, 300, 1, 'Háztartás', '4MAX_szemetes_60l_26_db.jpg', 1, 1),
(33, 'Tesco Sea Breeze légfrissítő 300 ml', 'Felfrissíti a levegőt, semlegesíti a szagokat, gyöngyvirág- és almavirág-illattal kombinált friss tengerillatot áraszt otthonában.', 459.00, 0, 30, 1, 'Háztartás', 'ts_sea_legfriss_0.3.jpg', 1, 1),
(34, 'Avouré Milk & Honey folyékony szappan 1 l', 'Aqua, Sodium Laureth Sulfate, Sodium Chloride, Cocamidopropyl Betaine, Glycerin, Coco Glucoside, Glyceryl Oleate, Tocopherol, Hydrogenated Palm Glycerides, Citrate, Glycol Distearate, Guar Hydroxypropyltrimonium Chloride, Mel, Lac Powder, Parfum, Citric A', 399.00, 1, 40, 1, 'Drogéria', 'avoure_milk_honey_folyekony_szappan.jpg', 2, 1),
(35, 'Baba lanolin krémtusfürdő 400 ml', 'Aqua, Sodium Laureth Sulfate, Sodium Chloride, Cocamidopropyl Betaine, Glycerin, Sodium Benzoate, Citric Acid, Parfum, Glycol Distearate, Laureth-4, PEG-75 Lanolin, Tetrasodium EDTA, Maris Sal, Benzyl Salicylate, Citrus Aurantium Peel Oil, Hexyl Cinnamal,', 1089.00, 0, 22, 1, 'Drogéria', 'baba_lanolin_kremtus_400ml.jpg', 2, 1),
(36, 'WU2 sampon normál és zsíros hajra 1000 ml', 'Aqua, Sodium Laureth Sulfate, Sodium Chloride, Cocamidopropyl Betaine, Cocamide DEA, Parfum, PEG-40 Hydrogenated Castor Oil, Imidazolidinyl Urea, Citric Acid, Magnesium Nitrate, Cinnamyl Alcohol, Eugenol, Methylchloroisothiazolinone, Magnesium Chloride, M', 1059.00, 1, 45, 1, 'Drogéria', 'wu2_sampon_normal_zsiros_1l.jpg', 2, 1),
(37, 'Lacalut White & Repair fogkrém 75 ml', 'Aqua, Hydrated Silica, Hydrogenated Starch Hydrolysate, Hydroxyapatite, Glycerin, Poloxamer 188, Sodium Lauryl Sulfate, Disodium Pyrophosphate, Aroma, Cellulose Gum, Aluminum Lactate, Pentasodium Triphosphate, Tetrapotassium Pyrophosphate, Sodium Myristyl', 1349.00, 0, 45, 1, 'Drogéria', 'lacalut_white_repair_75ml.jpg', 2, 1),
(38, 'Tesco Pro Formula vattakorong 120 db', '70%-ban pamut, valamint 30%-ban poliészter és cellulózszálak egyedülálló keveréke. Háromrétegű kozmetikai párna, amely nem válik szét, nem válnak le szálak belőle, és nem dörzsölődik szét. A külső rétegek első osztályú pamutból készültek, amelyek puha éri', 549.00, 120, 40, 1, 'Drogéria', 'ts_pro_vattakorong.jpg', 2, 1),
(39, 'Tesco Pro Formula Aloe Vera izzadásgátló dezodor 1', 'Butane, Propane, C12-15 alkyl benzoate, Isobutane, Isopropyl myristate, Aluminum chlorohydrate, Triethyl citrate, Parfum, Disteardimonium hectorite, Aloe barbadensis leaf juice, Maltodextrin, Geraniol, Benzyl salicylate, Alpha-isomethyl ionone, Limonene, ', 339.00, 0, 40, 1, 'Drogéria', 'tesco_pro_aloe_vera_dezodor.jpg', 2, 1),
(40, 'STR8 Game hajtógáz nélküli parfüm-spray 85 ml', 'Alcohol Denat., Aqua, Parfum, PEG-40 Hydrogenated Castor Oil, PEG-6 Caprylic/Capric Glycerides, Propylene Glycol, Ethylhexylglycerin, Tocopherol, BHT, Phenoxyethanol, Acetyl Cedrene, Alpha-Isomethyl Ionone, Anethole, Beta-Caryophyllene, Camphor, Citral, C', 2099.00, 0, 15, 1, 'Drogéria', 'str8_game_parfum_85ml.jpg', 2, 1),
(41, 'B.U. Absolute Me hajtógáz nélküli pumpás dezodor 7', 'Alcohol denat., Aqua, Parfum, PEG-40 hydrogenated castor oil, PEG-6 caprylic/capric glycerides, Propylene glycol, Benzotriazolyl dodecyl p-cresol, Butyl methoxydibenzoylmethane, Butylphthalimide, Ethylhexyl salicylate, Ethylhexylglycerin, Isopropylphthali', 2789.00, 0, 15, 1, 'Drogéria', 'bu_absolute_me_parfum_dezodor.jpg', 2, 1),
(42, 'Pro Formula Medium rugalmas nyakú fogkefe', 'Használati utasítás:\r\n- Az egészséges fogak és fogíny érdekében rendszeresen keresse fel fogorvosát.\r\n- Háromhavonta cseréljen fogkefét. Ezt a műanyag csomagolást 30%-ban újrahasznosított műanyagból készítettük.\r\nAz újrahasznosított műanyagok használatáva', 259.00, 1, 30, 1, 'Drogéria', 'ts_pro_medium_fogkefe.jpg', 2, 1),
(43, 'Listerine Cool Mint szájvíz 500 ml', 'Aqua, Alcohol, Sorbitol, Poloxamer 407, Benzoic acid, Sodium saccharin, Eucalyptol, Methyl salicylate, Thymol, Sodium benzoate, Menthol, Aroma, CI 42053, Alkoholt tartalmaz. Napi kétszeri használat mellett a Listerine® Cool Mint szájvíz javítja a szájhigi', 1549.00, 1, 45, 1, 'Drogéria', 'listerine_cool_mint_szajviz_0.5l.jpg', 2, 1),
(44, 'Vademecum 2in1 Junior eper ízű fogkrém és szájvíz ', 'Aqua, Sorbitol, Glycerin, Maltooligosyl Glucoside, Sodium Carboxymethyl Starch, Hydrogenated Starch Hydrolysate, Xanthan Gum, Calcium Glycerophosphate, Aroma, Sodium Laurylglucosides Hydroxypropylsulfonate, Sodium Fluoride, PEG-30 Glyceryl Stearate, Disod', 779.00, 0, 30, 1, 'Drogéria', 'vademecum_2in1_junior_eper_fogkrem_szajviz.jpg', 2, 1),
(45, 'Natural Chic hámlasztó kesztyű bambuszból és pamut', 'Használati útmutató: Finoman dörzsölje körkörös mozdulatokkal nedves bőrön.', 1299.00, 1, 20, 1, 'Drogéria', 'natural_chic_hamlaszto_kesztyu.jpg', 2, 1),
(46, 'Purina One száraz macskaeledel lazaccal 800g', 'Teljes értékű állateledel felnőtt macskák számára. Bifensis formulával az immunrendszer támogatásáért.', 2499.00, 800, 50, 1, 'Kisállat', 'purina_one_macska_lazac_800.jpg', 2, 1),
(47, 'Pedigree Markies Original kiegészítő állateledel f', 'Válogatott gabonafélék*, Hús és állati származékok, Különféle cukrok, Olajok és zsírok, Ásványi anyagok, Növényi eredetű származékok, Természetes összetevők. Ropogós, zamatos ízekkel teli kekszek - a Pedigree® Markies™ kívülről csodálatosan ropogós és bel', 1299.00, 500, 120, 0, 'Kisállat', 'pedigree_markies_og_kiegeszito_eledel_felnottnek_500.jpg', 2, 1),
(48, 'Pet Specialist illatmentes ásványi bentonit csomós', 'Kiváló nedvszívó képességű, természetes agyag alapú macskaalom, higiénikus megoldás minden napra.', 1599.00, 5, 15, 1, 'Kisállat', '1771154477_pet_spec_kutyaszalami_csirkes.jpg', 2, 1),
(49, 'Whiskas 1+ teljes értékű szárazeledel felnőtt macs', 'Válogatott gabonafélék, Hús és állati származékok (4% csirke a barna szemcsékben*), Olajok és zsírok, Növényi fehérje kivonatok, Növényi eredetű származékok, Ásványi anyagok, Zöldségek (0, 5% szárított sárgarépát tartalmaz (ami 4% sárgarépával egyenértékű', 3399.00, 1, 30, 1, 'Kisállat', 'whiskas_szaraz_felnott_csirkes_1400.jpg', 2, 1),
(50, 'Purina Felix Fantastic lazaccal/lepényhallal aszpi', 'Lazaccal aszpikban: hús és állati származékok* (12%), növényi fehérjekivonatok, hal és halszármazékok (lazac 4%), ásványi anyagok, különféle cukrok, Lepényhallal aszpikban: hús és állati származékok* (12%), növényi fehérjekivonatok, hal és halszármazékok ', 1039.00, 340, 30, 1, 'Kisállat', 'felix_fan_laz_lepeny_aszpik_340.jpg', 2, 1),
(51, 'Shelma száraz macskaeledel, macskatáp kölyök macsk', 'Csirkeliszt, Friss pulykahús (25%), Burgonyakeményítő, Állati zsír, Borsó (8%), Pulykaliszt, Burgonyapehely, Hidrolizált protein, Szárított céklapüré (3%), Szárított cikória (2%), Vitaminok és ásványi anyagok, Lazacolaj (0, 5%), Jukka, Spirulina, Szárítot', 2649.00, 750, 30, 1, 'Kisállat', 'shelma_macska_szaraz_kolyok_pulykas_750.jpg', 2, 1),
(52, 'Cesar teljes értékű nedves eledel felnőtt kutyák r', 'Hús és állati származékok (43%, amiből 92% természetes*, ebből 4% pulyka, 4% marha, 4% máj), Növényi eredetű származékok, Ásványi anyagok, Növényi fehérje kivonatok, Fűszerek (0, 08% rozmaring, 0, 02% petrezselyem), Zöldségek, *természetes összetevők.', 629.00, 150, 20, 1, 'Kisállat', 'cesar_felnott_kutyaeledel_pulyka_marha_150.jpg', 2, 1),
(53, 'Pet Specialist kutyaszalámi csirkével, teljes érté', 'Teljes értékű állateledel felnőtt kutyák részére, marhahúsos ízesítéssel.', 529.00, 1000, 40, 1, 'Kisállat', 'pet_spec_kutyaszalami_csirkes.jpg', 2, 1),
(54, 'Purina Friskies Junior csirkével, zöldségekkel és ', 'Gabonafélék (teljes kiőrlésű gabona 55%), Hús és állati származékok (15%, melyből 4% csirke), Olajok és zsírok, Növényi eredetű származékok, Növényi fehérjekivonatok, Ásványi anyagok, Zöldségek (0, 6% dehidratált zöldség, egyenértékű 4% zöldséggel), Tej é', 4399.00, 3000, 20, 1, 'Kisállat', 'friskies_junior_csirkes_zoldseg_tej_3kg.jpg', 2, 1),
(55, 'Reno húsos rágó kutya jutalomfalat marhában gazdag', 'Gabonafélék, Hús és állati származékok (marha 14%), Növényi eredetű származékok, Ásványi anyagok (kalcium-karbonát 0, 1%)\r\nKiegészítő állateledel a kutyád boldogságáért, vitaminokkal a vitalitás és jólét támogatására\r\nKutya jutalomfalat, 12 db / csomag\r\nK', 619.00, 12, 30, 1, 'Kisállat', 'reno_husos_rago_kutya_jutifalat_marhas_felnott.jpg', 2, 1),
(56, 'ICO színes ceruza készlet 12 db', 'Klasszikus minőség a kreativitáshoz!\r\nAz ICO Süni színes ceruzák generációk óta a gyerekek kedvencei. A készlet 12 darab élénk, intenzív színű ceruzát tartalmaz, amelyek kiváló fedőképességgel rendelkeznek. A ceruzatestek lakkozottak, hatszögletű kialakít', 1129.00, 12, 30, 1, 'Otthon-hobbi', 'ICO_szines_ceruza_12.jpg', 2, 1),
(57, 'Tesco Home Office puha nyelű olló 2 db', 'Főbb jellemzők\r\nRozsdamentes acél pengék\r\n21 cm és 12 cm-es pengék', 1779.00, 2, 20, 1, 'Otthon-hobbi', 'ts_home_ollo_2.jpg', 2, 1),
(58, 'Tesco Home E27 806 lm 60W 2700K LED klasszik izzó', '806 Lumen gömbfelületen\r\nNem fényszabályozható\r\nMeleg fehér\r\nKülső cégek logói\r\nCE jelölés - Európai Megfelelőség, FSC, WEEE Symbol (áthúzott kukát)', 1990.00, 3, 150, 1, 'Otthon-hobbi', 'tesco_led_izzo_808Im_60W_2700K_classic.jpg', 2, 1),
(59, 'Stabilo Excel F kék golyóstoll 4 db', 'Megbízhatóság és kényelem minden vonalban!\r\nA STABILO Excel F egy klasszikus, nyomógombos golyóstoll, amely az irodai és iskolai mindennapok elengedhetetlen kelléke. A készlet 4 darab azonos, kék színű tollat tartalmaz, így mindig lesz tartalékod a fontos', 869.00, 4, 20, 1, 'Otthon-hobbi', 'stabilo_excel_kek_toll_4.jpg', 2, 1),
(60, 'ICO négyzethálós füzet 27-32 A5', 'Az ICO Süni füzetek a hagyományos minőséget ötvözik a modern elvárásokkal. Ez az A5-ös méretű, négyzethálós füzet ideális választás matematika, fizika vagy egyéb számolást igénylő tantárgyakhoz. A füzet tartós borítóval és jó minőségű, fehér papírral rend', 235.00, 32, 20, 1, 'Otthon-hobbi', 'ICO_fuzet_a5_kockas_32.jpg', 2, 1),
(61, 'Loctite Super Bond Power Flex Gel univerzális gél ', 'A Loctite Super Bond Power Gél egy rendkívül erős, cseppmentes formulájú pillanatragasztó gél.\r\nA Loctite® Super Bond Power Gél egy cseppmentes formulájú, egyedi, gumi adalékanyagot tartalmazó pillanatragasztó gél, melynek köszönhetően rendkívül erős raga', 699.00, 2, 50, 1, 'Otthon-hobbi', 'loctite_power_flex_pillanatragaszto_gel_2g.jpg', 2, 1),
(62, 'Varta Energy AA LR6 1,5 V nagy teljesítményű alkál', 'Külső cégek logói\r\nFSC, Green Dot, Möbius-szalag, WEEE Symbol (áthúzott kukát).', 1099.00, 4, 45, 1, 'Otthon-hobbi', 'varta_elem_AA_1_5V.jpg', 2, 1),
(63, 'Decorata Party Happy Birthday léggömbök 6 db', 'Külső cégek logói\r\nCE jelölés - Európai Megfelelőség\r\nA csomag 6 darab prémium minőségű, tartós latexből készült léggömböt tartalmaz, amelyeket ünnepi „Happy Birthday” felirat díszít. A különböző színekben pompázó lufik azonnal feldobják a helyszín hangul', 1059.00, 6, 20, 1, 'Otthon-hobbi', 'decor_party_happy_bdday_lufi_6db.jpg', 2, 1),
(64, 'Reikel 5 m-es mérőszalag', 'A Reikel 5 méteres mérőszalag ideális választás mindazoknak, akik egy megbízható és könnyen kezelhető mérőeszközt keresnek otthoni barkácsoláshoz, felújításhoz vagy dekoráláshoz. A gumírozott, ütésálló háznak köszönhetően a szerszám jól bírja az igénybevé', 1799.00, 1, 20, 1, 'Otthon-hobbi', 'reikel_meroszalag_5m.jpg', 2, 1),
(65, 'Nohel Garden kötöző 23 cm 50 db', 'Kötöző - növényekre, virágokra és fákra többszöri felhasználás, nagyon gyors kötés, háztartásba is alkalmas', 1199.00, 50, 30, 1, 'Otthon-hobbi', 'nohel_garden_kotozo_50db_23cm.jpg', 2, 1),
(70, 'Jódozott konyhasó', 'Vákuumsó emberi fogyasztásra.', 180.00, 1000, 500, 1, 'Alapvető élelmiszerek', '1771101553_jodozott_so.jpg', 2, 1),
(71, 'Trappista sajt', 'Garantált minőségű, félkemény sajt.', 2800.00, 700, 30, 1, 'Tejtermék- tojás', '1771101755_trapista_sajt.jpeg', 2, 1),
(72, 'Pizza Margherita', 'Mélyhűtött, olaszos fűszerezésű pizza.', 1250.00, 350, 40, 1, 'Mélyhűtött', '1771101976_Pizza_Margherita.jpg', 2, 1),
(73, 'Sajtos pogácsa', 'Sok sajttal megszórt, omlós pogácsa.', 180.00, 80, 150, 1, 'Pékáru', '1771102180_Sajtos_pogacsa.jpg', 2, 1),
(74, 'Fürtös paradicsom', 'Érett, lédús fürtös paradicsom.', 890.00, 500, 80, 1, 'Zöldség és gyümölcs', '1771102263_furtos_paradicsom.jpeg', 2, 1),
(75, 'Debreceni kolbász', 'Enyhén csípős, sütnivaló kolbász.', 950.00, 300, 45, 1, 'Húsáru', '1771150242_debreceni_kolbasz.jpg', 2, 1),
(76, 'Burgonya', 'C-típusú, sütni való burgonya.', 380.00, 2500, 500, 1, 'Zöldség és gyümölcs', '1771150473_burgonya.jpg', 2, 1),
(77, 'Csirkemell filé', 'Konyhakész, friss csirkemell.', 2200.00, 1000, 40, 1, 'Húsáru', '1771150650_csirkemell_file.jpg', 2, 1),
(78, 'Görög joghurt', 'Natúr, krémes állagú joghurt.', 1189.00, 1000, 85, 1, 'Tejtermék- tojás', '1771150777_gorog_joghurt.jpeg', 2, 1),
(79, 'Szárazbab', 'Tarkabab levesekhez és főzelékekhez.', 899.00, 500, 65, 1, 'Alapvető élelmiszerek', '1771150955_szaraz_bab.jpg', 2, 1),
(80, 'Kristálycukor', 'Fehér finomított cukor.', 329.00, 1000, 300, 1, 'Alapvető élelmiszerek', '1771151124_kristalycukor.jpeg', 2, 1),
(81, 'Leveles tészta', 'Konyhakész, fagyasztott tészta.', 599.00, 500, 70, 1, 'Mélyhűtött', '1771151260_leveles_teszta.jpg', 2, 1),
(82, 'Banán', 'Édes, sárga banán közvetlenül az importőrtől.', 699.00, 1000, 300, 1, 'Zöldség és gyümölcs', '1771151395_banan.jpg', 2, 1),
(83, 'Szláv fonott kenyér', 'Kézzel vetett, kovászos fehér kenyér.', 889.00, 405, 40, 1, 'Pékáru', '1771151582_Szlav_fonott_kenyer.jpg', 2, 1),
(84, 'Nádudvari házias körözött', 'Ízesített túrókrém paprikával és hagymával.', 919.00, 135, 40, 1, 'Tejtermék- tojás', '1771151750_korozott.jpg', 2, 1),
(85, 'Spagetti száraztészta', 'Durumbúzából készült száraztészta.', 445.00, 500, 180, 1, 'Alapvető élelmiszerek', '1771152012_spaghetti_szarazteszta.jpg', 2, 1),
(86, 'Füstölt szalonna', 'Bükkfával füstölt, húsos kenyérszalonna.', 7090.00, 1000, 15, 1, 'Húsáru', '1771152200_fustolt_szalonna.jpg', 2, 1),
(87, 'Pulykamell sonka', 'Alacsony zsírtartalmú, szeletelt csemege.', 499.00, 100, 80, 1, 'Húsáru', '1771152343_pulykamell_sonka.jpg', 2, 1),
(88, 'Szilvás gombóc', 'Hagyományos recept alapján, fahéjjal.', 959.00, 600, 25, 1, 'Mélyhűtött', '1771152424_szilvas_gomboc.jpeg', 2, 1),
(89, 'Tejföl 20% zsírtartalmú', 'Sűrű, krémes magyar tejföl.', 499.00, 450, 150, 1, 'Tejtermék- tojás', '1771152580_tejfol_husz_szaz.jpg', 2, 1),
(90, 'Gála alma 4db', 'Friss, ropogós magyar étkezési alma.', 663.00, 750, 150, 1, 'Zöldség és gyümölcs', '1771152840_gala_alma.jpg', 2, 1),
(91, 'gyorsfagyasztott burgonya gerezdek', 'Sütőben is elkészíthető, fűszeres, fagyasztott burgonya.', 749.00, 750, 120, 1, 'Mélyhűtött', '1771153120_fagyasztott_hasab.jpg', 2, 1),
(92, 'Kakaós tej', 'Reggeli kedvenc, valódi kakaóval.', 599.00, 1000, 120, 1, 'Tejtermék- tojás', '1771153357_kakaos_tej.jpg', 2, 1),
(93, 'Túrós táska', 'Leveles tésztából, édes túrótöltelékkel.', 269.00, 100, 50, 1, 'Pékáru', '1771153477_turos_taska.jpg', 2, 1),
(94, 'Kaliforniai piros Paprika', 'Lédús, ropogós paprika.', 399.00, 200, 60, 1, 'Zöldség és gyümölcs', '1771153742_kaliforniai_piros.jpg', 2, 1),
(95, 'Kometa Bécsi virsli', 'Magas hústartalmú, juhbeles virsli.', 850.00, 400, 60, 1, 'Húsáru', '1771153929_becsi_virsli.jpg', 2, 1),
(96, 'Vénusz omega finomított étolaj', 'Finomított napraforgó étolaj, sütéshez.', 926.00, 1000, 250, 1, 'Alapvető élelmiszerek', '1771154127_venusz_etolaj.jpg', 2, 1),
(97, 'Kakaós csiga', 'Vajas tésztából, bőséges kakaós töltelékkel.', 185.00, 90, 60, 1, 'Pékáru', '1771154239_kakaos_csiga.jpg', 2, 1),
(98, 'Gelato Italiano gyorsfagyasztott Gesztenyepüré', 'Cukrozott, rumos aromával ízesítve.', 1149.00, 250, 55, 1, 'Mélyhűtött', '1771154476_gesztenyepure.jpg', 2, 1),
(99, 'Darált sertéshús 500g', 'Darált sertéshús.', 999.00, 500, 30, 1, 'Húsáru', '1771154709_daralt_sertes.jpg', 2, 1),
(100, 'Falni Jó! Csirke nuggets', 'Ropogós bundában.', 1799.00, 700, 40, 1, 'Mélyhűtött', '1771156282_csirke_nuggets_falni_jo.jpg', 2, 1),
(101, 'Meggle UHT Habtejszín', '30% zsírtartalmú habtejszín.', 739.00, 200, 55, 1, 'Tejtermék- tojás', '1771156455_Meggle_UHT_habtejszin.jpg', 2, 1),
(102, 'Foszlós kakaós kalács', 'Fonott kakaós kalács.', 699.00, 250, 20, 1, 'Pékáru', '1771156721_foszlos_kakaos_kalacs.jpg', 2, 1),
(103, 'Kígyóuborka', 'Friss magyar kígyóuborka.', 369.00, 350, 120, 1, 'Zöldség és gyümölcs', '1771156973_kigyouborka.jpg', 2, 1),
(104, 'Teljes kiőrlésű vekni', 'Rostban gazdag kenyér.', 659.00, 470, 25, 1, 'Pékáru', '1771157134_teljes_kiorlesu_kovaszos.jpg', 2, 1),
(105, 'UHT félzsíros tej 2,8%', 'Magyar tej.', 348.00, 1000, 200, 1, 'Tejtermék- tojás', '1771157438_tej_2,8_szazalek.jpg', 2, 1),
(106, 'Iglo halrudacska', '100% Alaszkai tőkehalból.', 2599.00, 420, 65, 1, 'Mélyhűtött', '1771157664_Iglo_gyorsfagyasztott_halrudacskak.jpg', 2, 1),
(107, 'Aranyfácán sűrített paradicsom', '22-24% sűrített paradicsom.', 759.00, 150, 90, 1, 'Alapvető élelmiszerek', '1771157898_aranyfacan_suritett_paradicsom.jpg', 2, 1),
(108, 'Hungária téliszalámi 350 g', 'Füstölt téliszalámi.', 1659.00, 350, 20, 1, 'Húsáru', '1771158176_Hungaria_teliszalami_350 g.jpg', 2, 1),
(109, 'Vaj 82% 100 g', 'Állati eredetű vaj.', 289.00, 100, 60, 1, 'Tejtermék- tojás', '1771158326_Vaj_82_szazalekos.jpeg', 2, 1),
(110, 'gyorsfagyasztott zöldborsó', 'Zsenge zöldborsó.', 339.00, 450, 95, 1, 'Mélyhűtött', '1771158531_zoldborso_fagyasztott.jpeg', 2, 1),
(111, 'Jázmin rizs 500 g', '„A” minőségű jázmin rizs.', 499.00, 500, 140, 1, 'Alapvető élelmiszerek', '1771158753_jazmin_rizs.jpg', 2, 1),
(112, 'Fehér fejeskáposzta', 'Kemény fejű téli káposzta.', 181.00, 1000, 90, 1, 'Zöldség és gyümölcs', '1771158898_feher_fejeskaposzta.jpg', 2, 1),
(113, 'Francia bagett XXL', 'Francia jellegű fehér kenyér.', 369.00, 280, 45, 1, 'Pékáru', '1771159062_francia_bagett.jpg', 2, 1),
(114, 'The Grower\'s Harvest vöröshagyma', 'Válogatott vöröshagyma.', 229.00, 1000, 120, 1, 'Zöldség és gyümölcs', '1771159262_voroshagyma_egy_kilo.jpg', 2, 1),
(115, 'Gyorsfagyasztott zöldségkeverék', 'Sárgarépa, brokkoli, karfiol.', 499.00, 450, 110, 1, 'Mélyhűtött', '1771159751_vegyes_zoldsegek_fagyasztott.jpeg', 2, 1),
(116, 'darabolt sertés comb', 'Csont nélkül, vákuumcsomagolt.', 1689.00, 1000, 25, 1, 'Húsáru', '1771159992_darabolt_sertes_comb.jpg', 2, 1),
(117, 'Klenáncz virágméz', 'Cseppmentes kupakkal.', 1859.00, 500, 30, 1, 'Alapvető élelmiszerek', '1771160419_Klenancz_virágmez.jpg', 2, 1),
(118, 'Nagyi titka búzafinomliszt', 'BL-55 típusú.', 299.00, 1000, 380, 1, 'Alapvető élelmiszerek', '1771160648_nagyititka_buzafinomliszt.jpg', 2, 1),
(119, 'Carte d\'Or Áfonyás álom', 'Jégkrém áfonyás szósszal.', 2499.00, 900, 20, 1, 'Mélyhűtött', '1771160962_Carte_d\'Or_Jegkrem_Afonyas_alom.jpg', 2, 1),
(120, 'Tesco közepes méretű tojás', 'Friss tojás, naponta gyűjtve.', 969.00, 530, 100, 1, 'Tejtermék- tojás', '1771161369_kozepes_meretu_tojas.jpg', 2, 1),
(121, 'Nosztalgia kifli', 'Hagyományos recept alapján.', 149.00, 75, 100, 1, 'Pékáru', '1771161575_nosztalgia_kifli.jpg', 2, 1),
(122, 'Friss csirke alsócomb', 'Friss csirke alsócomb.', 909.00, 1000, 90, 1, 'Húsáru', '1771161928_friss_csirkecomb.jpg', 2, 1),
(123, 'Citrom 500g', 'Lédús citrom.', 599.00, 500, 100, 1, 'Zöldség és gyümölcs', '1771162112_citrom_500g.jpg', 2, 1),
(124, 'Natúr szendvics zsemle', 'Natúr zsemle.', 69.00, 55, 300, 1, 'Pékáru', '1771162396_natur_szendvics_zsemle.jpg', 2, 1),
(125, 'Paco étkezési lencse', 'Válogatott lencse.', 739.00, 500, 75, 1, 'Alapvető élelmiszerek', '1771162664_Paco_etkezesi_lencse.jpg', 2, 1),
(126, 'Marha lábszár', 'Csont nélkül, pörkölthöz.', 4199.00, 1000, 12, 1, 'Húsáru', '1771162899_marha_labszar_csont_nelkuli.jpg', 2, 1),
(127, 'Tolle rögös túró', 'Félzsíros rögös túró.', 44900.00, 250, 45, 1, 'Tejtermék- tojás', '1771163056_tolle_felzsiros_rogos_turo.jpg', 2, 1),
(128, 'Pisztáciás töltött fánk', 'Pisztáciás krémmel.', 359.00, 69, 85, 1, 'Pékáru', '1771163382_pisztacias_fank.jpg', 2, 1),
(129, 'Gránátalma', 'Friss gyümölcs.', 699.00, 300, 50, 1, 'Zöldség és gyümölcs', '1771163987_granatalma.jpg', 2, 1);

-- --------------------------------------------------------

--
-- Tábla szerkezet ehhez a táblához `shopping_list`
--

CREATE TABLE `shopping_list` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `product_price` decimal(10,2) NOT NULL,
  `quantity` int(11) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- A tábla adatainak kiíratása `shopping_list`
--

INSERT INTO `shopping_list` (`id`, `user_id`, `product_id`, `product_price`, `quantity`) VALUES
(7, 3, 3, 890.00, 1),
(8, 4, 2, 499.00, 23),
(38, 4, 1, 499.00, 1);

-- --------------------------------------------------------

--
-- Tábla szerkezet ehhez a táblához `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `name` varchar(50) NOT NULL,
  `userName` varchar(50) NOT NULL,
  `email` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `admin` varchar(20) NOT NULL DEFAULT 'user',
  `profile_pic` varchar(255) DEFAULT 'default_user.png'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- A tábla adatainak kiíratása `users`
--

INSERT INTO `users` (`id`, `name`, `userName`, `email`, `password`, `admin`, `profile_pic`) VALUES
(1, 'valami', 'valami', 'valami@hotmail.com', '$2y$10$pNVhxRayvnlgreujtjYi0.wBay07v27dr0gkyMMwCE0GtQyeZzKWq', 'user', 'default_user.png'),
(2, 'Kovács Antal', 'thinkjan', 'kovacsan@gmail.com', 'password123', 'user', 'default_user.png'),
(3, 'Admin', 'admin', 'admin@bolt.hu', '$2y$10$w5C0UJMfwkrOUfuKnymCzumLIUJRuPIpYUQUTRAIbIJxJwvWA4Nv6', 'admin', 'default_user.png'),
(4, 'Admin', 'admin', 'admin@bolt.hu', '$2y$10$RAFXEyDNtXTb2mbi8cINj.j6PnZqVh1QJBm3Pt8abT/ZrvIuiZVVi', 'user', 'prof_1769776487.png'),
(5, 'vasarlo1', 'Béla', 'elsovas@hotmail.com', '$2y$10$c9ssHKwXXjWsxganFUwwAO3d96Ai2s4/h9RapOZUCWLn5/kE9e1Z6', 'user', 'prof_1770143785.jpg'),
(7, 'emailtest', 'email', 'gergo.mokan@gmail.com', '$2y$10$VdVK0hd16IWeM0U.qqt6leHiTBg2aJoGQ3mgsU28yQWm0ez2LwCva', 'user', 'default_user.png'),
(8, 'Példa János', 'Janos', 'janos.kis@gmail.com', '$2y$10$iFGGPSmG9/o0hwXBby2BBetCVTHhqfoU7JQa6y.hUxGpNR0NCNx22', 'user', 'default_user.png');

--
-- Indexek a kiírt táblákhoz
--

--
-- A tábla indexei `favorites`
--
ALTER TABLE `favorites`
  ADD PRIMARY KEY (`user_id`,`product_id`),
  ADD UNIQUE KEY `unique_fav` (`user_id`,`product_id`);

--
-- A tábla indexei `firm`
--
ALTER TABLE `firm`
  ADD PRIMARY KEY (`ID`),
  ADD KEY `idx_brand_name` (`brand_name`);

--
-- A tábla indexei `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_notifications_firm` (`firm_id`);

--
-- A tábla indexei `password_resets`
--
ALTER TABLE `password_resets`
  ADD PRIMARY KEY (`id`);

--
-- A tábla indexei `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`ID`),
  ADD KEY `firm_id` (`firm_id`),
  ADD KEY `price` (`price`),
  ADD KEY `price_2` (`price`),
  ADD KEY `idx_product_name` (`name`);

--
-- A tábla indexei `shopping_list`
--
ALTER TABLE `shopping_list`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_user_id` (`user_id`),
  ADD KEY `fk_product_id` (`product_id`);

--
-- A tábla indexei `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`);

--
-- A kiírt táblák AUTO_INCREMENT értéke
--

--
-- AUTO_INCREMENT a táblához `firm`
--
ALTER TABLE `firm`
  MODIFY `ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1310040629;

--
-- AUTO_INCREMENT a táblához `notifications`
--
ALTER TABLE `notifications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT a táblához `password_resets`
--
ALTER TABLE `password_resets`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT a táblához `products`
--
ALTER TABLE `products`
  MODIFY `ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=130;

--
-- AUTO_INCREMENT a táblához `shopping_list`
--
ALTER TABLE `shopping_list`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=39;

--
-- AUTO_INCREMENT a táblához `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- Megkötések a kiírt táblákhoz
--

--
-- Megkötések a táblához `notifications`
--
ALTER TABLE `notifications`
  ADD CONSTRAINT `fk_notifications_firm` FOREIGN KEY (`firm_id`) REFERENCES `firm` (`ID`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
