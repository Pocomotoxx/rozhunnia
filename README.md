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

Az utóbbi két végpont a felhasználók hozzáadására és törlésére szolgál. A kéréshez `X-Role` fejléc is szükséges,
mely a hívó jogosultsági szintjét adja meg.

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
