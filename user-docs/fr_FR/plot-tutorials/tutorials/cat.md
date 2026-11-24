---
title: Chat
description: "Quand le tracé commence, chaque mouvement est dessiné comme avec un stylo."
order: 0
translated_from: de
source_hash: 'cacc4d7e5d723466090b8a3dbe48bb58a84f93246f51767d6fcd3fa905131588'
---

[Chat (PDF, en allemand)](../../../media/plot-tutorials/Katze.pdf)

Quand le tracé commence, chaque mouvement est dessiné comme avec un stylo. Ce sont les lignes que le traceur découpe ensuite.

Notre objet est tourné dans la direction 150 degrés.

Dans ce tutoriel, on fait d’abord le côté droit de la tête du chat. Si tu regardes bien, la courbe est forte au début : l’objet doit donc tourner plus après chaque petite ligne. Ensuite, les virages deviennent plus petits pour le bas de la tête. Le côté gauche de la tête est de nouveau très courbé. C’est pour ça que cette partie de la tête utilise trois boucles en tout.

Ensuite vient l’oreille gauche. Elle est faite seulement de lignes droites, donc on peut utiliser la brique « Avancer de \_\_ pas ».

La partie entre les oreilles est de nouveau un arc. On la programme avec une autre boucle.

Ensuite, on fait l’oreille droite.

Le tracé est maintenant fini. Termine-le avec la brique « Arrêter de tracer » et enregistre-le sur ton téléphone avec « Enregistrer le tracé en SVG \_\_\_ ».

Tu peux maintenant envoyer le fichier SVG à un traceur (de découpe).
