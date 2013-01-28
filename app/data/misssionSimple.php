<?php
return array(
    1 => array(
        "title" => "Ieškomas samdomas žudikas",
        "des" => "%1 prašo nužudyti %2",
        "money" => 100,
        "exp" => 8,
        "skill" => array(
            "HUNT" => 0.75
        ),
        'win' => "Jūs sėkmingai nužudėte %2. Uždirbote %3 agonų, bei %4 patirties",
        'half' => "Sužeidėte %2, tačiau %1 jums sumokėjo dalį sumos kad galėtumete pabėgti",
        "no" => "Deja, jūs neįvykdėte užduoties. Jums trūksta medžioklės įgūdžio",
        "dificulity" => 1,
        "minlvl" => 25
    ),
    2 => array(
        "title" => "Pavogti HETA kuro",
        "des" => "%1 prašo pavogti HETA kuro iš UETS",
        "money" => 120,
        "exp" => 5,
        "skill" => array(
            "BREAKING" => 2
        ),
        'win' => "Jūs sėkmingai pavogėte HETA kuro. %1 užmokėjo %3 agonų, bei gavote %4 patirties",
        'half' => "Sugebėjote pavogti tik dalį kuro, už kurį %1 užmokėjo %3 agonų",
        "no" => "Deja, jūs neįvykdėte užduoties. Jums trūksta įsilaužimo įgūdžio",
        "dificulity" => 1,
        "minlvl" => 25
    ),
    3 => array(
        "title" => "Asmens sargybinis",
        "des" => "%1 prašo apsaugoti kelionėje į sostinę",
        "money" => 110,
        "exp" => 6,
        "skill" => array(
            "ORIENTACIJA" => 2.2
        ),
        'win' => "Nepriekaištingai atlikote savo užduotį, todėl %1 užmokėjo %3 agonų, bei gavote %4 patirties",
        'half' => "Sukėlėte pavojingą situaciją, už kurį %1 užmokėjo %3 agonų",
        "no" => "Deja, jūs neįvykdėte užduoties. Gal būt jums trūkstą orientacijos įgūdžio",
        "dificulity" => 1,
        "minlvl" => 25
    ),
    4 => array(
        "title" => "Sunkvežimių apiplėšimas",
        "des" => "%1 nori kad jūs sužlūgdytumėte HETA kuro siuntą į UETS",
        "money" => 80,
        "exp" => 8,
        "skill" => array(
            "BREAKING" => 2
        ),
        'win' => "Be vargo sunaikinote visą vilkstine, todėl %1 užmokėjo %3 agonų, bei gavote %4 patirties",
        'half' => "Dalis vilkstinės paspruko nuo jūsų. %1 labai supyko, tačiau užmokėjo %3 agonų",
        "no" => "Deja, jūs neįvykdėte užduoties. Gal būt jums trūkstą įsilaužimo įgūdžio",
        "dificulity" => 1,
        "minlvl" => 25
    ),
    5 => array(
        "title" => "Slapta kiber ataka",
        "des" => "%1 jūsų prašo gauti informacijos apie UETS užsakymus",
        "money" => 150,
        "exp" => 4,
        "skill" => array(
            "HACKING" => 1.5
        ),
        'win' => "UETS apsauga ne jūsų lygiui. Per 5 minutes gavote visą info. %1 labai patenkintas ir užmokėjo %3 agonų, bei gavote %4 patirties",
        'half' => "UETS privertė jus paprakaituoti, ir vėlavote su užduotimi. %1 buvo ramus, tačiau užmokėjo %3 agonų, nes vėlavote",
        "no" => "Deja, jūs neįvykdėte užduoties. Gal būt jums trūkstą Kibernetikos įgūdžio",
        "dificulity" => 1,
        "minlvl" => 25
    ),
    6 => array(
        "title" => "Super slapta kiber ataka",
        "des" => "%1 jūsų prašo suklastoti UETS užsakymus",
        "money" => 200,
        "exp" => 7,
        "skill" => array(
            "HACKING" => 1
        ),
        'win' => "UETS apsauga ne jūsų lygiui. Per 3 minutes gavote visą info. %1 labai patenkintas ir užmokėjo %3 agonų, bei gavote %4 patirties",
        'half' => false,
        "no" => "Deja, jūs neįvykdėte užduoties. Gal būt jums trūkstą Kibernetikos įgūdžio",
        "dificulity" => 4,
        "minlvl" => 100
    ),
    7 => array(
        "title" => "Ginklų vagystė",
        "des" => "%1 nori kad jūs, iš UST parneštumėte labai retų HETA ginklų",
        "money" => 250,
        "exp" => 9,
        "skill" => array(
            "BREAKING" => 0.9,
            "ESCAPE" => 0.35,
            "HIDING" => 0.4,
            "ORIENTACIJA" => 0.2
        ),
        'win' => "Niekas net nesuodė kad jūs tai padarėte. %1 labai patenkintas ir užmokėjo %3 agonų, bei gavote %4 patirties",
        'half' => "Kažkas aktivavo signalizaciją, ir jūs turėjote pasprukti. Kaip bebūtų, %1 sumokėjo už ginklą",
        "no" => "Deja, jūs neįvykdėte užduoties. Gal būt jums trūkstą įsilaužimo, orientacijos arba pabėgimo įgūdžio",
        "dificulity" => 5,
        "minlvl" => 220
    ),
    8 => array(
        "title" => "UST bazės šturmavimas",
        "des" => "%1 nori kad jūs su jo komanda padėtumėte šturmuoti UST",
        "money" => 300,
        "exp" => 10,
        "skill" => array(
            "BREAKING" => 0.9,
            "ESCAPE" => 0.35,
            "HIDING" => 0.4,
            "ORIENTACIJA" => 0.5
        ),
        'win' => "Ištaškėte visą pasipriešinimą. %1 labai patenkintas ir užmokėjo %3 agonų, bei gavote %4 patirties",
        'half' => "Deja, pasipriešinimas buvo stipresnis negu tikėjotės. Kaip bebūtų, %1 sumokėjo už ginklą",
        "no" => "Deja, buvote sulaikytas. Visa laimė, kad pasinaudojote proga ir pasprukote. Gal būt jums trūkstą įsilaužimo, orientacijos arba pabėgimo įgūdžio",
        "dificulity" => 5,
        "minlvl" => 250
    ),
    9 => array(
        "title" => "Susekti vagį",
        "des" => "UST prašo surasti vagį",
        "money" => 200,
        "exp" => 5,
        "skill" => array(
            "HUNT" => 0.8,
            "ORIENTACIJA" => 0.6
        ),
        'win' => "Per pusvalandį susekėte %1, kuris kartu su %2 vogė amuniciją. UTS užmokėjo %3 agonų, bei gavote %4 patirties",
        'half' => false,
        "no" => "Deja, buvote neužčiuopėte jokių galų. Gal būt jums trūkstą medžioklės, orientacijos įgūdžių",
        "dificulity" => 3,
        "minlvl" => 200
    ),
    10 => array(
        "title" => "Šturmuoti 8 bunkerį",
        "des" => "UST vadas nori kad padėtumėte šturmuoti 8 bunkerį",
        "money" => 320,
        "exp" => 5,
        "skill" => array(
            "BREAKING" => 0.82,
            "ESCAPE" => 0.30,
            "HIDING" => 0.32,
            "SPEED" => 0.4
        ),
        'win' => "Ėjote kiaurai priešų fronto liniją ir užėmėte 8 bunkerį. UTS užmokėjo %3 agonų, bei gavote %4 patirties",
        'half' => "Nors pradžia buvo puiki, tačiau suklupote pusiaukelėje. UST kompensavo žalą, užmokėdama %3 agonų",
        "no" => "Deja 8 unkerio ginyba buvo jums neįveikiama.",
        "dificulity" => 6,
        "minlvl" => 230
    ),
    11 => array(
        "title" => "Šturmuoti 9 bunkerį",
        "des" => "UST vadas nori kad padėtumėte šturmuoti 8 bunkerį",
        "money" => 320,
        "exp" => 5,
        "skill" => array(
            "BREAKING" => 0.79,
            "ESCAPE" => 0.29,
            "HIDING" => 0.31,
            "SPEED" => 0.38
        ),
        'win' => "Ėjote kiaurai priešų fronto liniją ir užėmėte 9 bunkerį. UTS užmokėjo %3 agonų, bei gavote %4 patirties",
        'half' => "Nors pradžia buvo puiki, tačiau suklupote pusiaukelėje. UST kompensavo žalą, užmokėdama %3 agonų",
        "no" => "Deja 8 unkerio ginyba buvo jums neįveikiama.",
        "dificulity" => 6,
        "minlvl" => 270
    ),
	12 => array(
		"title" => "Šturmuoti 10 bunkerį",
		"des" => "UST vadas nori kad padėtumėte šturmuoti 8 bunkerį",
		"money" => 320,
		"exp" => 5,
        "skill" => array(
            "BREAKING" => 0.77,
            "ESCAPE" => 0.27,
            "HIDING" => 0.29,
            "SPEED" => 0.36
        ),
		'win' => "Ėjote kiaurai priešų fronto liniją ir užėmėte 10 bunkerį. UTS užmokėjo %3 agonų, bei gavote %4 patirties",
		'half' => "Nors pradžia buvo puiki, tačiau suklupote pusiaukelėje. UST kompensavo žalą, užmokėdama %3 agonų",
		"no" => "Deja 8 unkerio ginyba buvo jums neįveikiama.",
        "dificulity" => 6,
        "minlvl" => 290
	),
    13 => array(
        "title" => "Super slapta kiber ataka",
        "des" => "%1 jūsų prašo pavogti UETS ginklų duomenis",
        "money" => 210,
        "exp" => 7,
        "skill" => array(
            "HACKING" => 1
        ),
        'win' => "UETS apsauga ne jūsų lygiui. Per 3 minutes gavote visą info. %1 labai patenkintas ir užmokėjo %3 agonų, bei gavote %4 patirties",
        'half' => false,
        "no" => "Deja, jūs neįvykdėte užduoties. Gal būt jums trūkstą Kibernetikos įgūdžio",
        "dificulity" => 4,
        "minlvl" => 110
    ),
    14 => array(
        "title" => "Super slapta kiber ataka",
        "des" => "%1 jūsų prašo pavogti UETS implantų duomenis",
        "money" => 220,
        "exp" => 7,
        "skill" => array(
            "HACKING" => 0.9
        ),
        'win' => "UETS apsauga ne jūsų lygiui. Per 3 minutes gavote visą info. %1 labai patenkintas ir užmokėjo %3 agonų, bei gavote %4 patirties",
        'half' => false,
        "no" => "Deja, jūs neįvykdėte užduoties. Gal būt jums trūkstą Kibernetikos įgūdžio",
        "dificulity" => 4,
        "minlvl" => 115
    )
);
?>
