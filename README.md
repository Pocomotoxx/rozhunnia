# Telemedicina Platform

Ez a projekt egy egyszerű telemedicina alkalmazás statikus HTML felülettel és minimális PHP alapú backenddel.

## Backend futtatása

A `server.php` fájl egy beépített PHP szerverrel futtatható, amely kiszolgálja a `telemedicine-html-app.html` oldalt és több egyszerű API végpontot biztosít. A backend minimalista MVC szemléletet követ: a `lib/` könyvtárban található osztályok (`Database`, `Auth`, `Logger`) kezelik az adatbázis‑kapcsolatot, a jogosultságot és a naplózást.

```bash
php -S localhost:8000 server.php
```

Ezt követően a felület a `http://localhost:8000/` címen érhető el, míg az API állapotát a `http://localhost:8000/api/status` végpont adja vissza.

Az alábbi funkcionalitások külön `/api/...` végpontokon érhetők el, és mindegyik `X-API-Key: secret123` fejlécet igényel:

- `/api/dashboard` – dinamikus statisztikák a tárolt adatok alapján
- `/api/terapiak` – GET: listázás, POST: új terápia hozzáadása, DELETE `/api/terapiak/<id>`: törlés
- `/api/gyogyszerek` – GET/POST/DELETE hasonló módon
- `/api/chat`
- `/api/ertesitesek` – GET/POST/DELETE
- `/api/betegek` – GET: összes beteg, POST: új beteg, DELETE `/api/betegek/<id>`: törlés
- `/api/users/add?role=<szerep>`
- `/api/users/delete?role=<szerep>`
- `/api/patients/<id>/chart`
- `/api/patients/<id>/caregiver` (POST: `?caregiver=<nev>` hozzárendelés, DELETE: eltávolítás)
- `/api/users/<user>/vacation?on=1|0`

Az `/api/chat` végpont közösségi üzenőfalat biztosít négy kategóriával: általános rendszerüzenetek, partnerek szerint,
ellátotti szervezetek és privát üzenetek. A híváshoz `X-Role` fejléc szükséges, gondozók esetén pedig `X-User` is, hogy a
saját privát üzeneteiket láthassák. A rendszergazda minden üzenetet lát, az admin az általános és partner kategóriákat, a
gondozó pedig az általános üzeneteket és a saját privát üzeneteit.

Az egyes modulok adatai egy SQLite adatbázisban (`data.sqlite`) kerülnek tárolásra, amelynek sémája a `schema.sql` fájlban dokumentált. A `Database` osztály automatikusan betölti a sémát és példányosításkor létrehozza a szükséges táblákat és mintadatokat. A `/api/dashboard` végpont mindig az aktuális elemszámokat adja vissza.

Az utóbbi két felhasználó-kezelő végpont a felhasználók hozzáadására és törlésére szolgál. A kéréshez `X-Role` fejléc is
szükséges, mely a hívó jogosultsági szintjét adja meg.

A gondozók hozzárendelése a `/api/patients/<id>/caregiver` végponton történik. Csak rendszergazda vagy admin jogosult rá, a
gondozó eltávolítását is ugyanezen végpont `DELETE` metódusa kezeli. A `?caregiver=` query paraméterrel adható meg a
hozzárendelendő gondozó neve.

A szerepkörök szabadság státusza a `/api/users/<user>/vacation` végponton állítható. Admin és rendszergazda bármely felhasználó
szabadságát kezelheti, míg más szerepkörök csak a sajátjukat kapcsolhatják a `X-User` fejléccel azonosítva.

### Jogosultsági szintek

- **rendszergazda** – bárkit hozzáadhat vagy törölhet
- **admin** – gondozó, gyógyszerész vagy beteg felhasználókat kezelhet, rendszergazdát nem
- **gondozo**
- **gyogyszeresz**
- **beteg**

A gondozók és gyógyszerészek felületén egyaránt elérhető a **Gyógyszerek** és a **Terápiák** menüpont.

## Tesztek futtatása

A kódbázis kétféle tesztet tartalmaz; a backend tesztek a `server.log` fájlba írt naplóbejegyzéseket is ellenőrzik:

```bash
pytest -q      # Python tesztek
npm test       # Node.js alapú ellenőrzés
```

Mindkét parancs sikeres lefutása igazolja, hogy a footer és a backend megfelelően működik.

## Terápiás lap

A `patient-therapy.html` oldal egy egyszerű digitális karton, amely megjeleníti a beteg nevét, gyógyszereit, betegségeit, terápiait és a gondozót. Az oldal az `/api/patients/<id>/chart` végpontot hívja meg a szükséges adatokért (például: `/api/patients/patient1/chart`).
