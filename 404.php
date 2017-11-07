<html>
<head>
<title>Figg Disch Und Stirb</title>
</head>
<body>
<h1><?php

$headlines = Array(
    "Geh kacken!",
    "Heiß, schwarz, und echt lecker!",
    "Du stinkst ja Junge, echt ey, da geh ich lieber mal nen paar Schritte zurück, bevor ich mir noch den Anzug bekotze!",
    "Ich glaub ich muss gleich ma echt wild um mich wüten Junge",
    "Ich tret' dir deine fiesen Zähne einzeln aus der Fresse"
);

shuffle($headlines);
echo $headlines[0];

?></h1>
<p>Diese Fehlermeldung wird Ihnen präsentiert von <?php

$user = Array(
    "DMI",
    "Gartenzwerg",
/*    "Dante",
    "Psycho",
    "CrazyCat",
    "Aenn",
    "SirDuplo",
    "LastZero",
    "l0wrid3r",
    "henk3r",
    "SirBB",
    "TheMastar",
    "Asso",*/
);

shuffle($user);
echo $user[0];

?> und:</p>
<p><?php

$sponsors = Array(
    "HOMBACHER - Die Pferdesalami", 
    "Knallmayr Prohomo - Jetzt auch als Zäpfchen!", 
    "Microsoft Windoof wiXP Homo Edition",
    "SpreizP(r)o - Das Analspekulum für den Genießer",
    "<pre>         /     \             \            /    \\
        |       |             \          |      |
        |       `.             |         |       :
        `        |             |        \|       |
         \       | /       /  \\\\\\   --__ \\\\       :
          \      \/   _--~~          ~--__| \     |
           \      \_-~                    ~-_\    |
            \_     \        _.--------.______\|   |
              \     \______// _ ___ _ (_(__>  \   |
               \   .  C ___)  ______ (_(____>  |  /
               /\ |   C ____)/      \ (_____>  |_/
              / /\|   C_____)       |  (___>   /  \\
             |   (   _C_____)\______/  // _/ /     \\
             |    \  |__   \\\\_________// (__/       |
            | \    \____)   `----   --'             |
            |  \_          ___\       /_          _/ |
           |              /    |     |  \            |
           |             |    /       \  \           |
           |          / /    |         |  \           |
           |         / /      \__/\___/    |          |
          |         / /        |    |       |         |
          |          |         |    |       |         |</pre>",
    "Subway - Eat Fresh!",
    "McDonalds - Ich Liebe Es!",
    "Clearasil - Für Benni nur das Beste",
    "Gilette - Für das Beste im Mann",
    "GEZ - Schon abGEZockt?",
    "USA World Domination Tour - Bombing a country near you",
    "oekos - Die Schule für Deutsch",
    "THW - Trinken, Helfen, Weitersaufen",
    "Beerdigungsinstitut IKEA - wohnst du schon oder lebst du noch?",
    "Hakle Feucht",
    "Flutschi Anal - Für die Lust durchs Hinterstübchen.",
    "Jürgen Domian - Die einzige Person, die ihnen vor dem Suizid noch zuhört",
    "Bundeswehr - Lern schiessen, treff' Freunde",
    "1337 - Da werden Sie gegangen",
);

shuffle($sponsors);
echo $sponsors[0];

?></p>
</body>
</html>
