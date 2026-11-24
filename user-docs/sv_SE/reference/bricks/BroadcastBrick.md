---
source_hash: '506490e42f1db106d6247eedb85c4a03ab23d2028796ed83688a2d63eb2964b8'
---

![Skicka](../../../media/bricks/broadcast2-1.png)

Skickar ett meddelande till andra delar av ditt projekt och säger åt dem att starta sina egna skript. Det är som att göra ett tillkännagivande som alla lyssnar på.

**Varför skicka meddelanden?**

Med meddelanden kan olika objekt i en scen prata med varandra och påverka varandra. Ett objekt kan skicka ett meddelande till alla andra objekt, och de som tar emot det kan reagera på meddelandet.

Meddelandena **visas inte** på skärmen! Om du vill visa text använder du blocken i kategorin ”Utseende”!

**Så här fungerar det**

- *Avsändaren:* Avsändaren startar kommunikationen med ett *”Skicka”*-block. Blocket skickar ett meddelande till alla andra objekt i scenen (även bakgrunden).
- *Mottagaren:* Mottagaren är objektet som lyssnar och reagerar på meddelanden. Det använder blocket *”När du tar emot”* för att märka när ett visst meddelande kommer.
- *Hantering av meddelandet:* När mottagaren får meddelandet kör den alla block som ligger under *”När du tar emot”*. Så kan du bestämma vad som ska hända när ett visst meddelande kommer.
