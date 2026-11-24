---
source_hash: 'a08ec5cbeea4f82605553c61fb59a6a99b5b54299004e04f34fb01f5625ca763'
---

![När du tar emot](../../../media/bricks/when-you-receive-__2.png)

Med det här blocket lyssnar ditt objekt efter ett visst meddelande och kör skriptet när meddelandet kommer. Bra för att låta objekt prata med varandra och säga till när de ska göra något.

![Skicka](../../../media/bricks/broadcast2.png)

**Varför skicka meddelanden?**

Med meddelanden kan olika objekt i en scen prata med varandra och påverka varandra. Ett objekt kan skicka ett meddelande till alla andra objekt, och de som tar emot det kan reagera på meddelandet.

Meddelandena **visas inte** på skärmen! Om du vill visa text använder du blocken i kategorin ”Utseende”!

**Så här fungerar det**

- *Avsändaren:* Avsändaren startar kommunikationen med ett *”Skicka”*-block. Blocket skickar ett meddelande till alla andra objekt i scenen (även bakgrunden).
- *Mottagaren:* Mottagaren är objektet som lyssnar och reagerar på meddelanden. Det använder blocket *”När du tar emot”* för att märka när ett visst meddelande kommer.
- *Hantering av meddelandet:* När mottagaren får meddelandet kör den alla block som ligger under *”När du tar emot”*. Så kan du bestämma vad som ska hända när ett visst meddelande kommer.
