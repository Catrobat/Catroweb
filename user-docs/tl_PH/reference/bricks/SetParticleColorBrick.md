---
source_hash: 'f59fe963d22959f01b6b52114a000b84b3ea10677929561e0f4c48c8bfd78a0b'
---
![I-set ang kulay ng mga partikulo sa "#ff0000"](../../../media/bricks/Set_particle_colour_to_ff0000.png)

Ang bloke ng code na ito ay nagbabago ng kulay ng mga partikulo na pinalabas ng aktor. Para bang baguhin ang kulay ng mga confetti sa isang party popper.

Ang isang hex code color ay isang paraan upang ilarawan ang mga kulay gamit ang hexadecimal na halaga. Ito'y binubuo ng anim na character, na maaaring bilang (0-9) o mga titik (A-F). Ang code ay karaniwang nakasulat na may “#” sa simula, tulad ng ganito: `#RRGGBB`.

- Ang unang dalawang character (`RR`) ay kumakatawan sa pulang bahagi ng kulay.
- Ang susunod na dalawang character (`GG`) ay kumakatawan sa berde na bahagi.
- Ang huling dalawang character (`BB`) ay kumakatawan sa asul na bahagi.

Ang bawat pares ng mga character ay maaaring mag-range mula sa `00` (na nangangahulugang walang kulay na iyon) hanggang `FF` (na nangangahulugang ang maximum na halaga ng kulay na iyon). Halimbawa:

`#000000` ay itim (walang kulay).

`#FF0000` ay purong pula.

`#00FF00` ay purong berde.

`#0000FF` ay purong asul.

`#FFFFFF` ay puti (mahigit na pulang, berde, at asul).
