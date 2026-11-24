---
source_hash: 'a08ec5cbeea4f82605553c61fb59a6a99b5b54299004e04f34fb01f5625ca763'
---

![when-you-receive-__2](../../../media/bricks/when-you-receive-__2.png)

Met dit blok luistert je object naar een bepaald bericht. Als het bericht binnenkomt, start het script. Handig om objecten met elkaar te laten praten en om ze te vertellen wat ze moeten doen.

![broadcast2](../../../media/bricks/broadcast2.png)

**Waarom berichten sturen?**

Met berichten kunnen verschillende objecten in een scène met elkaar praten. Één object kan een bericht naar alle andere objecten sturen. De objecten die het bericht krijgen, kunnen daar dan op reageren.

Deze berichten worden **niet getoond** op het scherm! Wil je tekst op het scherm tonen, gebruik dan de blokken in de categorie “Uiterlijk”!

**Hoe werkt het?**

- *Verzendend object:* Het verzendende object start de communicatie met een *“Zend bericht”*-blok. Dit blok stuurt een bericht naar alle andere objecten in de scène (ook de achtergrond).
- *Ontvangend object:* Het ontvangende object luistert naar berichten en reageert erop. Het gebruikt het *“Bij ontvangst van”*-blok om te merken wanneer een bepaald bericht binnenkomt.
- *Berichten verwerken:* Als het ontvangende object het bericht krijgt, voert het alle blokken uit die onder het *“Bij ontvangst van”*-blok staan. Zo bepaal je wat er gebeurt als een bepaald bericht binnenkomt.
