---
title: Kucing
description: "Saat plot dimulai, setiap gerakan digambar seperti dengan pena."
order: 0
translated_from: de
source_hash: 'cacc4d7e5d723466090b8a3dbe48bb58a84f93246f51767d6fcd3fa905131588'
---

[Kucing (PDF, bahasa Jerman)](../../../media/plot-tutorials/Katze.pdf)

Saat plot dimulai, setiap gerakan digambar seperti dengan pena. Ini adalah garis yang nanti dipotong oleh plotter.

Objek kita menghadap ke arah 150 derajat.

Dalam tutorial ini, sisi kanan kepala kucing dibuat dulu. Kalau kamu perhatikan dengan teliti, lengkungannya kuat di awal, jadi objek harus berbelok lebih banyak setelah setiap garis kecil. Lalu belokannya makin kecil untuk bagian bawah kepala. Sisi kiri kepala melengkung kuat lagi. Itu sebabnya bagian kepala ini memakai tiga loop secara total.

Berikutnya adalah telinga kiri. Telinga ini hanya terdiri dari garis lurus, jadi kita bisa memakai blok "Gerak \_\_ langkah".

Bagian di antara telinga adalah lengkungan lagi dan diprogram dengan loop lain.

Lalu telinga kanan dibuat.

Plot sekarang sudah selesai. Akhiri dengan blok "Berhenti memplot" dan simpan di ponselmu dengan "Simpan plot sebagai SVG \_\_\_".

Sekarang kamu bisa mengirim file SVG ke plotter (pemotong).
