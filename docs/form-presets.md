# Form Presets per School Level

`FormPresetSeederService` seeds a ready-to-use form into `ppdb_form_fields` based on the school level. The committee can then edit, hide, or add fields in the form builder.

## Summary

| Tier | Level | Required Documents | Optional Documents |
| :---: | :--- | :--- | :--- |
| 1 | PAUD / TK | none | all (deliver later) |
| 2 | SD / MI | KK, Akta Kelahiran | certificates |
| 3 | SMP / MTs | KK, Akta, SKL | |
| 4 | SMA / SMK | KK, Akta, Ijazah, Rapor scan | |

---

## Tier 1: PAUD / TK

**Preset fields**
- Nama panggilan
- Anak ke-
- Berat / tinggi badan
- Catatan kesehatan khusus
- Ukuran seragam (`S`, `M`, `L`, `XL`)
- WhatsApp ibu

**Documents:** all uploads optional / deliver later.

## Tier 2: SD / Madrasah Ibtidaiyah

**Preset fields**
- Nama lengkap
- NIK
- Nomor KK
- Jarak ke sekolah
- Moda transportasi
- Data ayah dan ibu

**Documents:** KK and Akta Kelahiran required. Certificates optional.

## Tier 3: SMP / Madrasah Tsanawiyah

**Preset fields**
- NISN
- NPSN sekolah asal
- Nama sekolah asal
- Rata-rata nilai rapor (semester 1–5)
- Ukuran seragam

**Documents:** KK, Akta, SKL (Surat Keterangan Lulus).

## Tier 4: SMA / SMK

**Preset fields**
- Pilihan jurusan (1 dan 2)
- Nilai akademik kumulatif
- Tinggi badan dan status buta warna (for technical majors)

**Documents:** KK, Akta, Ijazah, Rapor scan.
