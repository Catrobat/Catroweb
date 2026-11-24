---
source_hash: '506490e42f1db106d6247eedb85c4a03ab23d2028796ed83688a2d63eb2964b8'
---

![Send besked](../../../media/bricks/broadcast2-1.png)

Sender en besked til andre dele af dit projekt, så de begynder at køre deres egne scripts. Det er som at lave en meddelelse, som alle lytter til.

**Hvorfor sende beskeder?**

Med beskeder kan du kommunikere og samarbejde mellem forskellige objekter i en scene. Et objekt kan sende en besked til alle andre objekter, og de objekter, der modtager beskeden, kan reagere på den.

Disse beskeder **vises ikke** på skærmen! Hvis du vil vise beskeder, så brug klodserne i kategorien “Udseende”!

**Hvordan virker det?**

- *Afsenderobjekt:* Afsenderobjektet starter kommunikationen ved at bruge en *“Send besked”*-klods. Klodsen sender en besked til alle andre objekter i scenen (også baggrunden).
- *Modtagerobjekt:* Modtagerobjektet er det, der lytter efter og reagerer på beskeder. Det bruger klodsen *“Når du modtager”* til at opdage, når en bestemt besked bliver modtaget.
- *Beskedhåndtering:* Når modtagerobjektet får beskeden, kører det alle klodser under *“Når du modtager”*. Det giver dig mulighed for at bestemme, hvad der skal ske, når en bestemt besked modtages.
