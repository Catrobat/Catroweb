---
source_hash: '978919f7559e59b9f547ad44c92a64e21656d9dec0783eb01d71dde5af072686'
---

![brodcast_wait_brick3](../../../media/bricks/brodcast_wait_brick3.png)

Stuurt een bericht naar andere delen van je project en wacht dan tot ze klaar zijn voordat je verder gaat. Het is alsof je een vriend iets laat doen en wacht tot hij klaar is voordat je verdergaat.

**Waarom berichten sturen?**

Met berichten kunnen verschillende objecten in een scène met elkaar praten. Één object kan een bericht naar alle andere objecten sturen. De objecten die het bericht krijgen, kunnen daar dan op reageren.

Deze berichten worden **niet getoond** op het scherm! Wil je tekst op het scherm tonen, gebruik dan de blokken in de categorie “Uiterlijk”!

**Hoe werkt het?**

- *Verzendend object:* Het verzendende object start de communicatie met een *“Zend bericht”*-blok. Dit blok stuurt een bericht naar alle andere objecten in de scène (ook de achtergrond).
- *Ontvangend object:* Het ontvangende object luistert naar berichten en reageert erop. Het gebruikt het *“Bij ontvangst van”*-blok om te merken wanneer een bepaald bericht binnenkomt.
- *Berichten verwerken:* Als het ontvangende object het bericht krijgt, voert het alle blokken uit die onder het *“Bij ontvangst van”*-blok staan. Zo bepaal je wat er gebeurt als een bepaald bericht binnenkomt.
