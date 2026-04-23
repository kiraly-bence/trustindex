# Trustindex – Véleménygyűjtő alkalmazás

Symfony alapú webalkalmazás, amellyel cégekről lehet véleményeket beküldeni és böngészni. A főoldalon megtekinthetők az összes beérkezett vélemény, valamint egy űrlap segítségével új vélemény adható le. A `/companies` oldalon cégenkénti összesítő látható az értékelések számával és az átlagos értékeléssel.

## Követelmények

- PHP 8.4+
- Composer
- MySQL 8.0+

## Telepítés

**1. Függőségek telepítése**
```bash
composer install
```

**2. Környezeti konfiguráció**

Hozz létre egy `.env.local` fájlt a projekt gyökerében, és add meg az adatbázis kapcsolati adatokat:
```
DATABASE_URL="mysql://felhasznalo:jelszo@127.0.0.1:3306/trustindex?serverVersion=8.4.3&charset=utf8mb4"
```

## Adatbázis létrehozása és migrációk futtatása

**Adatbázis létrehozása** (ha még nem létezik):
```bash
php bin/console doctrine:database:create
```

**Migrációk futtatása:**
```bash
php bin/console doctrine:migrations:migrate
```

**Tesztadatok betöltése** (opcionális):
```bash
php bin/console doctrine:fixtures:load
```

## Fejlesztői szerver indítása

```bash
symfony serve
```

Az alkalmazás ezután elérhető a `https://127.0.0.1:8000` címen.

**Alternatíva** (Symfony CLI nélkül):
```bash
php -S localhost:8000 -t public
```

## Munkaidő napló

- **1. Adatmodell (Doctrine ORM):** 20 perc
- **2.1 Új vélemény beküldése:** 20 perc
- **2.2 Vélemények listázása:** 20 perc
- **2.3 Vélemény részletező oldal:** 10 perc
- **2.4 Összesített cégstatisztika:** 30 perc
- **2.5 Keresés cég neve alapján:** 10 perc