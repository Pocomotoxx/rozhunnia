# Telemedicina Platform

Ez a projekt egy egyszerű telemedicina alkalmazás statikus HTML felülettel és minimális PHP alapú backenddel.

## Backend futtatása

A `server.php` fájl egy beépített PHP szerverrel futtatható, amely kiszolgálja a `telemedicine-html-app.html` oldalt és egy egyszerű
API végpontot biztosít.

```bash
php -S localhost:8000 server.php
```

Ezt követően a felület a `http://localhost:8000/` címen érhető el, míg az API állapotát a `http://localhost:8000/api/status` végpont adja vissza.

## Tesztek futtatása

A kódbázis kétféle tesztet tartalmaz:

```bash
pytest -q      # Python tesztek
npm test       # Node.js alapú ellenőrzés
```

Mindkét parancs sikeres lefutása igazolja, hogy a footer és a backend megfelelően működik.
