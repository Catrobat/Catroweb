---
source_hash: '978919f7559e59b9f547ad44c92a64e21656d9dec0783eb01d71dde5af072686'
---

![brodcast_wait_brick3](../../../media/bricks/brodcast_wait_brick3.png)

Wysyła wiadomość do innych części twojego projektu, a potem czeka, aż skończą swoje zadania, zanim pójdzie dalej. To jak poproszenie kolegi o zrobienie czegoś i czekanie, aż skończy, zanim ty zaczniesz dalej.

**Po co wysyłać wiadomości?**

Dzięki wiadomościom różne obiekty w scenie mogą się ze sobą komunikować i na siebie reagować. Jeden obiekt może wysłać wiadomość do wszystkich innych obiektów, a obiekty, które ją odbiorą, mogą na nią zareagować.

Te wiadomości **nie są wyświetlane** na ekranie! Jeśli chcesz coś wyświetlić, użyj klocków z kategorii „Wygląd”!

**Jak to działa?**

- *Obiekt nadawca:* Obiekt nadawca rozpoczyna komunikację, używając klocka *„Nadaj”*. Ten klocek wysyła wiadomość do wszystkich innych obiektów w scenie (także do tła).
- *Obiekt odbiorca:* Obiekt odbiorca nasłuchuje wiadomości i na nie reaguje. Używa klocka *„Kiedy otrzymasz”*, żeby wykryć, kiedy przychodzi konkretna wiadomość.
- *Obsługa wiadomości:* Kiedy obiekt odbiorca dostanie wiadomość, wykona wszystkie klocki pod klockiem *„Kiedy otrzymasz”*. Dzięki temu możesz określić, co ma się stać, gdy przyjdzie dana wiadomość.
