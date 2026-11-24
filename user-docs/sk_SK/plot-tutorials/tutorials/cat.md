---
title: Mačka
description: "Keď sa plot spustí, každý pohyb sa kreslí, akoby perom."
order: 0
translated_from: de
source_hash: 'cacc4d7e5d723466090b8a3dbe48bb58a84f93246f51767d6fcd3fa905131588'
---

[Mačka (PDF, nemecky)](../../../media/plot-tutorials/Katze.pdf)

Keď sa plot spustí, každý pohyb sa kreslí, akoby perom. Sú to čiary, pozdĺž ktorých ich plotter neskôr vyreže.

Náš objekt smeruje pod uhlom 150 stupňov.

V tomto návode sa najprv vytvorí pravá strana mačacej hlavy. Ak sa pozorne pozrieš, na začiatku je krivka silná, takže objekt sa musí po každej malej čiare otočiť viac. Potom sa zákruty pre spodnú časť hlavy zmenšujú. Ľavá strana hlavy je znovu silno zakrivená. Preto táto časť hlavy používa celkovo tri slučky.

Ďalej nasleduje ľavé ucho. Je tvorené len rovnými čiarami, takže môžeme použiť blok „Pohni sa o __ krokov“.

Časť medzi ušami je znovu oblúk a naprogramuje sa ďalšou slučkou.

Potom sa vytvorí pravé ucho.

Plot je teraz hotový. Ukonči ho blokom „Zastav kreslenie“ a ulož ho do telefónu pomocou „Ulož plot ako SVG ___“.

Teraz môžeš poslať súbor SVG na (rezací) plotter.
