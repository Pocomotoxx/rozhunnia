# Telemedicina Platform

Ez a projekt egy egyszerű telemedicina alkalmazás statikus HTML felülettel és minimális PHP alapú backenddel.

## Backend futtatása

A `server.php` fájl egy beépített PHP szerverrel futtatható, amely kiszolgálja a `telemedicine-html-app.html` oldalt és több egyszerű API végpontot biztosít.

```bash
php -S localhost:8000 server.php
```

Ezt követően a felület a `http://localhost:8000/` címen érhető el, míg az API állapotát a `http://localhost:8000/api/status` végpont adja vissza.

Az alábbi funkcionalitások külön `/api/...` végpontokon érhetők el, és mindegyik `X-API-Key: secret123` fejlécet igényel:

- `/api/dashboard`
- `/api/terapiak`
- `/api/gyogyszerek`
- `/api/chat`
- `/api/ertesitesek`
- `/api/betegek`
- `/api/users/add?role=<szerep>`
- `/api/users/delete?role=<szerep>`
- `/api/patients/<id>/chart`

Az `/api/chat` végpont közösségi üzenőfalat biztosít négy kategóriával: általános rendszerüzenetek, partnerek szerint,
ellátotti szervezetek és privát üzenetek. A híváshoz `X-Role` fejléc szükséges, gondozók esetén pedig `X-User` is, hogy a
saját privát üzeneteiket láthassák. A rendszergazda minden üzenetet lát, az admin az általános és partner kategóriákat, a
gondozó pedig az általános üzeneteket és a saját privát üzeneteit.

Az utóbbi két felhasználó-kezelő végpont a felhasználók hozzáadására és törlésére szolgál. A kéréshez `X-Role` fejléc is
szükséges, mely a hívó jogosultsági szintjét adja meg.

### Jogosultsági szintek

- **rendszergazda** – bárkit hozzáadhat vagy törölhet
- **admin** – gondozó, gyógyszerész vagy beteg felhasználókat kezelhet, rendszergazdát nem
- **gondozo**
- **gyogyszeresz**
- **beteg**

## Tesztek futtatása

A kódbázis kétféle tesztet tartalmaz:

```bash
pytest -q      # Python tesztek
npm test       # Node.js alapú ellenőrzés
```

Mindkét parancs sikeres lefutása igazolja, hogy a footer és a backend megfelelően működik.

## Terápiás lap

A `patient-therapy.html` oldal egy egyszerű digitális karton, amely megjeleníti a beteg nevét, gyógyszereit, betegségeit, terápiait és a gondozót. Az oldal az `/api/patients/<id>/chart` végpontot hívja meg a szükséges adatokért (például: `/api/patients/patient1/chart`).
