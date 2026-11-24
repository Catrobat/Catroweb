---
source_hash: '506490e42f1db106d6247eedb85c4a03ab23d2028796ed83688a2d63eb2964b8'
---

![Send melding](../../../media/bricks/broadcast2-1.png)

Sender en melding til andre deler av prosjektet ditt, og ber dem starte sine egne skript. Det er som å lage en kunngjøring som alle hører.

**Hvorfor sende meldinger?**

Med meldinger kan ulike objekter i en scene snakke sammen og samhandle. Ett objekt kan sende en melding til alle andre objekter, og de som mottar meldingen kan reagere på den.

Disse meldingene **vises ikke** på skjermen! Hvis du vil vise meldinger, bruk klossene i kategorien «Utseende»!

**Hvordan fungerer det?**

- *Avsenderobjekt:* Avsenderobjektet starter kommunikasjonen med en *«Send melding»*-kloss. Denne klossen sender en melding til alle andre objekter i scenen (også bakgrunnen).
- *Mottakerobjekt:* Mottakerobjektet lytter etter meldinger og reagerer på dem. Det bruker klossen *«Når du mottar»* for å se når en bestemt melding kommer.
- *Håndtering av meldingen:* Når mottakerobjektet får meldingen, kjører det alle klossene under *«Når du mottar»*. Da kan du lage egne handlinger som skjer når en bestemt melding kommer.
