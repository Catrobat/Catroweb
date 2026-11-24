---
source_hash: '506490e42f1db106d6247eedb85c4a03ab23d2028796ed83688a2d63eb2964b8'
---

![broadcast2](../../../media/bricks/broadcast2-1.png)

Stuurt een bericht naar andere delen van je project. Die delen beginnen dan met hun eigen scripts. Het is alsof je iets omroept en iedereen luistert mee.

**Waarom berichten sturen?**

Met berichten kunnen verschillende objecten in een scène met elkaar praten. Één object kan een bericht naar alle andere objecten sturen. De objecten die het bericht krijgen, kunnen daar dan op reageren.

Deze berichten worden **niet getoond** op het scherm! Wil je tekst op het scherm tonen, gebruik dan de blokken in de categorie “Uiterlijk”!

**Hoe werkt het?**

- *Verzendend object:* Het verzendende object start de communicatie met een *“Zend bericht”*-blok. Dit blok stuurt een bericht naar alle andere objecten in de scène (ook de achtergrond).
- *Ontvangend object:* Het ontvangende object luistert naar berichten en reageert erop. Het gebruikt het *“Bij ontvangst van”*-blok om te merken wanneer een bepaald bericht binnenkomt.
- *Berichten verwerken:* Als het ontvangende object het bericht krijgt, voert het alle blokken uit die onder het *“Bij ontvangst van”*-blok staan. Zo bepaal je wat er gebeurt als een bepaald bericht binnenkomt.
