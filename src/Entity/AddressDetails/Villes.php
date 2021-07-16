<?php

namespace App\Entity\AddressDetails;

class Villes
{
    private static $villes;
    private static $initialized = false;

    private static function initialize()
    {
        if (self::$initialized)
            return;

        self::$villes = [
            new Ville('Béja', [
                new Municipality('Béja'),
                new Municipality('Ouchtata-Jmila'),
                new Municipality('Medjez el-Bab'),
                new Municipality('Testour'),
                new Municipality('Téboursouk'),
                new Municipality('Thibar'),
                new Municipality('Sidi Ismaïl'),
                new Municipality('El Maâgoula'),
                new Municipality('Slouguia'),
                new Municipality('Nefza'),
                new Municipality('Amdoun'),
                new Municipality('Goubellat'),


            ]),


            new Ville('Ben Arous', [
                new Municipality('El Mourouj'),
                new Municipality('Ben Arous'),
                new Municipality('Mohamedia'),
                new Municipality('Radès'),
                new Municipality('Fouchana'),
                new Municipality('Hammam Lif'),
                new Municipality('Bou Mhel el-Bassatine'),
                new Municipality('Ezzahra'),
                new Municipality('Hammam Chott'),
                new Municipality('Mornag'),
                new Municipality('Naassen'),
                new Municipality('Mégrine'),
                new Municipality('Khalidia'),

            ]),

            new Ville('Bizerte', [
                new Municipality('Bizerte'),
                new Municipality('Menzel Bourguiba'),
                new Municipality('Mateur'),
                new Municipality('Joumine'),
                new Municipality('Ras Jebel'),
                new Municipality('Ghezala'),
                new Municipality('Menzel Jemil'),
                new Municipality('Tinja'),
                new Municipality('Utique'),
                new Municipality('Menzel Abderrahmane'),
                new Municipality('El Hachachna'),
                new Municipality('El Alia'),
                new Municipality('Metline'),
                new Municipality('Raf Raf'),
                new Municipality('Sejnane'),
                new Municipality('Ghar El Melh'),
                new Municipality('Aousja'),

            ]),

            new Ville('Gabès', [
                new Municipality('Gabès'),
                new Municipality('El Hamma'),
                new Municipality('Ghannouch'),
                new Municipality('Teboulbou'),
                new Municipality('Habib Thameur Bouatouch'),
                new Municipality('Chenini Nahal'),
                new Municipality('Kettana'),
                new Municipality('Bouchemma'),
                new Municipality('Mareth'),
                new Municipality('Métouia'),
                new Municipality('Dkhilet Toujane'),
                new Municipality('Menzel El Habib'),
                new Municipality('Oudhref'),
                new Municipality('Nouvelle Matmata'),
                new Municipality('Zarat'),
                new Municipality('Matmata'),

            ]),

            new Ville('Gafsa', [
                new Municipality('Gafsa'),
                new Municipality('Métlaoui'),
                new Municipality('El Ksar'),
                new Municipality('Redeyef'),
                new Municipality('Moularès'),
                new Municipality('Zannouch'),
                new Municipality('Belkhir'),
                new Municipality('El Guettar'),
                new Municipality('Mdhilla'),
                new Municipality('Lela'),
                new Municipality('Sidi Aïch'),
                new Municipality('Sened'),
                new Municipality('Sidi Boubaker'),

            ]),


            new Ville('Jendouba', [
                new Municipality('Jendouba'),
                new Municipality('Balta-Bou Aouane'),
                new Municipality('Bou Salem'),
                new Municipality('Tabarka'),
                new Municipality('Ghardimaou'),
                new Municipality('Jouaouda'),
                new Municipality('Aïn Sobh-Nadhour'),
                new Municipality('Kalaa-Maaden-Farksan'),
                new Municipality('Souk Sebt'),
                new Municipality('Khmairia'),
                new Municipality('Aïn Draham'),
                new Municipality('Fernana'),
                new Municipality('Oued Meliz'),
                new Municipality('Beni M\'Tir'),

            ]),


            new Ville('Kairouan', [
                new Municipality('Kairouan'),
                new Municipality('Sisseb-Driaat'),
                new Municipality('Raqqada'),
                new Municipality('Abida'),
                new Municipality('Aïn El Beïdha'),
                new Municipality('Chaouachi'),
                new Municipality('Hajeb El Ayoun'),
                new Municipality('Chraitia-Ksour'),
                new Municipality('Jhina'),
                new Municipality('Oueslatia'),
                new Municipality('Haffouz'),
                new Municipality('Sbikha'),
                new Municipality('Bou Hajla'),
                new Municipality('Nasrallah'),
                new Municipality('Menzel Mehiri'),
                new Municipality('El Alâa'),
                new Municipality('Chebika'),
                new Municipality('Aïn Djeloula'),
                new Municipality('Echrarda'),
            ]),

            new Ville('Kasserine', [
                new Municipality('Kasserine'),
                new Municipality('Ennour'),
                new Municipality('Fériana'),
                new Municipality('Sbeïtla'),
                new Municipality('Chrayaa-Machrek Chams'),
                new Municipality('Ezzouhour'),
                new Municipality('Hassi El Ferid'),
                new Municipality('El Ayoun'),
                new Municipality('Thala'),
                new Municipality('Rakhmat'),
                new Municipality('Bouzguem'),
                new Municipality('Aïn Khmaissia'),
                new Municipality('Khmouda'),
                new Municipality('Foussana'),
                new Municipality('Thélepte'),
                new Municipality('Sbiba'),
                new Municipality('Majel Bel Abbès'),
                new Municipality('Jedelienne'),
                new Municipality('Haïdra'),

            ]),

            new Ville('Kébili', [
                new Municipality('Douz'),
                new Municipality('Kébili'),
                new Municipality('Souk Lahad'),
                new Municipality('Faouar'),
                new Municipality('Bechli-Blidet-Jerssin'),
                new Municipality('Bechri-Fatnassa'),
                new Municipality('El Golâa'),
                new Municipality('Jemna'),
                new Municipality('Rjim Maatoug'),

            ]),

            new Ville('l\'Ariana', [
                new Municipality('La Soukra'),
                new Municipality('Ariana'),
                new Municipality('Raoued'),
                new Municipality('Mnihla'),
                new Municipality('Ettadhamen'),
                new Municipality('Kalâat el-Andalous'),
                new Municipality('Sidi Thabet'),

            ]),

            new Ville('la Manouba', [
                new Municipality('Douar Hicher'),
                new Municipality('Oued Ellil'),
                new Municipality('La Manouba'),
                new Municipality('Djedeida'),
                new Municipality('Tebourba'),
                new Municipality('Den Den'),
                new Municipality('Mornaguia'),
                new Municipality('El Bassatine'),
                new Municipality('Borj El Amri'),
                new Municipality('El Batan'),

            ]),

            new Ville('Mahdia', [
                new Municipality('Mahdia'),
                new Municipality('Ksour Essef'),
                new Municipality('Ouled Chamekh'),
                new Municipality('Chebba'),
                new Municipality('El Jem'),
                new Municipality('Sidi Zid-Awled Moulahem'),
                new Municipality('Tlelsa'),
                new Municipality('Hkaima'),
                new Municipality('Zelba'),
                new Municipality('Rejiche'),
                new Municipality('Sidi Alouane'),
                new Municipality('Kerker'),
                new Municipality('El Bradâa'),
                new Municipality('Melloulèche'),
                new Municipality('Chorbane'),
                new Municipality('Essouassi'),
                new Municipality('Bou Merdes'),
                new Municipality('Hebira'),

            ]),

            new Ville('Médenine', [
                new Municipality('Ben Gardane'),
                new Municipality('Djerba - Houmt Souk'),
                new Municipality('Zarzis'),
                new Municipality('Médenine'),
                new Municipality('Djerba - Midoun'),
                new Municipality('Zarzis Nord'),
                new Municipality('Djerba - Ajim'),
                new Municipality('Boughrara'),
                new Municipality('Sidi Makhlouf'),
                new Municipality('Beni Khedache'),

            ]),

            new Ville('Monastir', [
                new Municipality('Monastir'),
                new Municipality('Moknine'),
                new Municipality('Jemmal'),
                new Municipality('Ksar Hellal'),
                new Municipality('Téboulba'),
                new Municipality('Ouerdanine'),
                new Municipality('Sahline Moôtmar'),
                new Municipality('Bekalta'),
                new Municipality('Zéramdine'),
                new Municipality('Bembla-Mnara'),
                new Municipality('Sidi Jedidi'),
                new Municipality('Bennane-Bodheur'),
                new Municipality('Ksibet el-Médiouni'),
                new Municipality('Sayada'),
                new Municipality('Menzel Hayet'),
                new Municipality('Menzel Ennour'),
                new Municipality('Khniss'),
                new Municipality('Beni Hassen'),
                new Municipality('Menzel Kamel'),
                new Municipality('Sidi Ameur-Mesjed-Aïssa'),
                new Municipality('Amiret Hajjaj'),
                new Municipality('Touza'),
                new Municipality('Zaouiet Kontoch'),
                new Municipality('Amiret Touazra'),
                new Municipality('Bouhjar'),
                new Municipality('Lamta'),
                new Municipality('Amiret El Fhoul'),
                new Municipality('El Ghnada'),
                new Municipality('El Masdour-Menzel Harb'),
                new Municipality('Sidi Bennour'),
                new Municipality('Cherahil'),
                new Municipality('Menzel Fersi'),

            ]),

            new Ville('Nabeul', [
                new Municipality('Hammamet'),
                new Municipality('Nabeul'),
                new Municipality('Kélibia'),
                new Municipality('Dar Chaâbane'),
                new Municipality('Menzel Temime'),
                new Municipality('Korba'),
                new Municipality('Soliman'),
                new Municipality('Grombalia'),
                new Municipality('Fondouk Jedid-Seltene'),
                new Municipality('Takelsa'),
                new Municipality('Béni Khiar'),
                new Municipality('Menzel Bouzelfa'),
                new Municipality('Béni Khalled'),
                new Municipality('Tazougrane-Boukrim-Zaouiet El Mgaies'),
                new Municipality('Chrifet-Boucharray'),
                new Municipality('Bou Argoub'),
                new Municipality('El Haouaria'),
                new Municipality('Tazarka'),
                new Municipality('Hammam Ghezèze'),
                new Municipality('El Maâmoura'),
                new Municipality('Zaouiet Djedidi'),
                new Municipality('Somâa'),
                new Municipality('Menzel Horr'),
                new Municipality('Azmour'),
                new Municipality('Dar Allouch'),
                new Municipality('El Mida'),
                new Municipality('Korbous'),

            ]),


            new Ville('Sfax', [
                new Municipality('Sfax'),
                new Municipality('Sakiet Ezzit'),
                new Municipality('Sakiet Eddaïer'),
                new Municipality('El Aïn'),
                new Municipality('Gremda'),
                new Municipality('Ouabed Khazanet'),
                new Municipality('Thyna'),
                new Municipality('El Amra'),
                new Municipality('Chihia'),
                new Municipality('Nadhour-Sidi Ali Ben Abed'),
                new Municipality('Hadjeb'),
                new Municipality('Hazeg Ellouza'),
                new Municipality('Aachech-Aouadna-Boujarbou-Majel Draj'),
                new Municipality('Ennasr'),
                new Municipality('Mahrès'),
                new Municipality('Kerkennah'),
                new Municipality('Skhira'),
                new Municipality('Agareb'),
                new Municipality('El Hencha'),
                new Municipality('Jebiniana'),
                new Municipality('Bir Ali Ben Khalifa'),
                new Municipality('Graïba'),
                new Municipality('Menzel Chaker'),

            ]),


            new Ville('Sidi Bouzid', [
                new Municipality('Sidi Bouzid'),
                new Municipality('Essaïda'),
                new Municipality('Faiedh Bennour'),
                new Municipality('Souk Jedid'),
                new Municipality('Lessouda'),
                new Municipality('Baten Ghzal'),
                new Municipality('Meknassy'),
                new Municipality('Regueb'),
                new Municipality('Mansoura'),
                new Municipality('Sidi Ali Ben Aoun'),
                new Municipality('Rahal'),
                new Municipality('Mezzouna'),
                new Municipality('Menzel Bouzaiane'),
                new Municipality('Bir El Hafey'),
                new Municipality('Jilma'),
                new Municipality('Cebbala Ouled Asker'),
                new Municipality('Ouled Haffouz'),

            ]),

            new Ville('Siliana', [
                new Municipality('Siliana'),
                new Municipality('Sidi Morched'),
                new Municipality('Makthar'),
                new Municipality('Bou Arada'),
                new Municipality('Gaâfour'),
                new Municipality('El Krib'),
                new Municipality('Hbabsa'),
                new Municipality('Bargou'),
                new Municipality('Rouhia'),
                new Municipality('Sidi Bou Rouis'),
                new Municipality('El Aroussa'),
                new Municipality('Kesra'),

            ]),


            new Ville('Sousse', [
                new Municipality('Sousse'),
                new Municipality('M\'saken'),
                new Municipality('Kalâa Kebira'),
                new Municipality('Hammam Sousse'),
                new Municipality('Kalâa Seghira'),
                new Municipality('Akouda'),
                new Municipality('Zaouiet Sousse'),
                new Municipality('Grimet-Hicher'),
                new Municipality('Ezzouhour'),
                new Municipality('Messaadine'),
                new Municipality('Ksibet Thrayet'),
                new Municipality('Enfida'),
                new Municipality('Sidi Bou Ali'),
                new Municipality('Bouficha'),
                new Municipality('Hergla'),
                new Municipality('Chott Meriem'),
                new Municipality('Kondar'),
                new Municipality('Sidi El Hani'),

            ]),


            new Ville('Tataouine', [
                new Municipality('Tataouine'),
                new Municipality('Smâr'),
                new Municipality('Ghomrassen'),
                new Municipality('Bir Lahmar'),
                new Municipality('Tataouine Sud'),
                new Municipality('Remada'),
                new Municipality('Dehiba'),

            ]),


            new Ville('Tozeur', [
                new Municipality('Tozeur'),
                new Municipality('Nefta'),
                new Municipality('Degache'),
                new Municipality('El Hamma du Jérid'),
                new Municipality('Hazoua'),
                new Municipality('Tamerza'),

            ]),


            new Ville('Tunis', [
                new Municipality('Tunis'),
                new Municipality('Sidi Hassine'),
                new Municipality('La Marsa'),
                new Municipality('Le Kram'),
                new Municipality('Le Bardo'),
                new Municipality('La Goulette'),
                new Municipality('Carthage'),
                new Municipality('Sidi Bou Saïd'),

            ]),

            new Ville('Zaghouan', [
                new Municipality('El Fahs'),
                new Municipality('Zaghouan'),
                new Municipality('Saouaf'),
                new Municipality('El Amaiem'),
                new Municipality('Zriba'),
                new Municipality('Bir Mcherga'),
                new Municipality('Nadhour'),
                new Municipality('Djebel Oust'),

            ]),


            new Ville('Kef', [
                new Municipality('Le Kef'),
                new Municipality('Tajerouine'),
                new Municipality('Dahmani'),
                new Municipality('Zaafrana-Dir '),
                new Municipality('Le Sers'),
                new Municipality('Jérissa'),
                new Municipality('Bohra'),
                new Municipality('Kalaat Senan'),
                new Municipality('Sakiet Sidi Youssef'),
                new Municipality('El Ksour'),
                new Municipality('El Marja'),
                new Municipality('Nebeur'),
                new Municipality('Kalâat Khasba'),
                new Municipality('Touiref'),
                new Municipality('Menzel Salem'),

            ]),

        ];

        self::$initialized = true;
    }

    public static function GET()
    {
        self::initialize();
        return self::$villes;;
    }
}

class Ville
{
    public $name;
    public $municipalities;
    function __construct($name, $municipalities)
    {
        $this->name = $name;
        $this->municipalities = $municipalities;
    }
}

class Municipality
{
    public $name;
    function __construct($name)
    {
        $this->name = $name;
    }
}
