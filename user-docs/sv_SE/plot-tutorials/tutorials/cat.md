---
title: Katt
description: "När plottern startar ritas varje rörelse som om du använde en penna."
order: 0
translated_from: de
source_hash: 'cacc4d7e5d723466090b8a3dbe48bb58a84f93246f51767d6fcd3fa905131588'
---

[Katt (PDF, tyska)](../../../media/plot-tutorials/Katze.pdf)

När plottern startar ritas varje rörelse som om du använde en penna. Det här är linjerna som plottern klipper längs senare.

Vårt objekt pekar i riktning 150 grader.

I den här tutorialen görs kattens högra sida av huvudet först. Om du tittar noga ser du att kurvan är kraftig i början, så objektet måste svänga mer efter varje liten linje. Sedan blir svängarna mindre för den nedre delen av huvudet. Kattens vänstra sida av huvudet böjer sig kraftigt igen. Därför använder den här delen av huvudet tre loopar i totalt.

Sedan kommer vänster öra. Det görs bara av raka linjer, så vi kan använda blocket ”Flytta __ steg”.

Delen mellan öronen är en båge igen och programmeras med en annan loop.

Sedan görs höger öra.

Plottningen är nu klar. Avsluta den med blocket ”Sluta plotta” och spara den på din telefon med ”Spara plotten som SVG __”.

Nu kan du skicka SVG-filen till en (skär)plotter.
