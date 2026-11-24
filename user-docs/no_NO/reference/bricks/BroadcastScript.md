---
source_hash: 'a08ec5cbeea4f82605553c61fb59a6a99b5b54299004e04f34fb01f5625ca763'
---

![Når du mottar](../../../media/bricks/when-you-receive-__2.png)

Med denne klossen lytter objektet ditt etter en bestemt melding og kjører skriptet når meldingen kommer. Nyttig for å la objekter snakke sammen og fortelle dem når de skal gjøre noe.

![Send melding](../../../media/bricks/broadcast2.png)

**Hvorfor sende meldinger?**

Med meldinger kan ulike objekter i en scene snakke sammen og samhandle. Ett objekt kan sende en melding til alle andre objekter, og de som mottar meldingen kan reagere på den.

Disse meldingene **vises ikke** på skjermen! Hvis du vil vise meldinger, bruk klossene i kategorien «Utseende»!

**Hvordan fungerer det?**

- *Avsenderobjekt:* Avsenderobjektet starter kommunikasjonen med en *«Send melding»*-kloss. Denne klossen sender en melding til alle andre objekter i scenen (også bakgrunnen).
- *Mottakerobjekt:* Mottakerobjektet lytter etter meldinger og reagerer på dem. Det bruker klossen *«Når du mottar»* for å se når en bestemt melding kommer.
- *Håndtering av meldingen:* Når mottakerobjektet får meldingen, kjører det alle klossene under *«Når du mottar»*. Da kan du lage egne handlinger som skjer når en bestemt melding kommer.
