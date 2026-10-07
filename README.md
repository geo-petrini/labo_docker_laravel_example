# Stack Laravel + PostgreSQL — CRUD

Template minimale per avviare un'applicazione **Laravel** (12) con un database **PostgreSQL**, e una piccola CRUD su una risorsa `items`.

Il progetto Laravel viene generato da `composer create-project` durante il build; una cartella `overlay/` aggiunge migrazione, model, controller e vista Blade per la CRUD.

## Struttura

```
laravel/
├── README.md
├── docker-compose.yml   # servizi app + db
├── Dockerfile           # build PHP + Laravel + overlay
├── entrypoint.sh        # key:generate + migrate + apache
└── overlay/             # copiato su /var/www/app/ dopo composer create-project
    ├── .env
    ├── routes/web.php
    ├── app/Models/Item.php
    ├── app/Http/Controllers/ItemController.php
    ├── database/migrations/2026_01_01_000000_create_items_table.php
    └── resources/views/index.blade.php
```

## docker-compose.yml

```yaml
services:
  app:
    build: .
    container_name: laravel-postgres-app
    ports:
      - "8020:80"
    environment:
      DB_CONNECTION: pgsql
      DB_HOST: db
      DB_PORT: 5432
      DB_DATABASE: laravel
      DB_USERNAME: laravel
      DB_PASSWORD: segreta
    depends_on:
      db:
        condition: service_healthy

  db:
    image: postgres:16
    container_name: laravel-postgres-db
    ports:
      - "5433:5432"
    environment:
      POSTGRES_USER: laravel
      POSTGRES_PASSWORD: segreta
      POSTGRES_DB: laravel
    volumes:
      - postgres-data:/var/lib/postgresql/data
    healthcheck:
      test: ["CMD-SHELL", "pg_isready -U laravel -d laravel"]
      interval: 5s
      timeout: 5s
      retries: 10

volumes:
  postgres-data:
```

## Concetti toccati

- **Build** — il `Dockerfile` scarica Laravel via `composer create-project`, installa le estensioni PHP (`pdo_pgsql`) e sovrascrive i file di `overlay/`.
- **Port mapping** — `"8020:80"` espone Apache sull'host; `"5433:5432"` espone PostgreSQL.
- **Variabili d'ambiente** — le credenziali vengono passate al compose e copiate in `overlay/.env`.
- **Volume nominato** — `postgres-data` rende persistenti i dati PostgreSQL.
- **Healthcheck** — l'app parte solo quando `pg_isready` riscontra che il DB è pronto.
- **Entrypoint** — al primo avvio vengono eseguiti `key:generate` e `migrate`, creando anche la tabella `items`.

## API CRUD (items)

| Metodo | Endpoint | Descrizione |
|---|---|---|
| `GET` | `/api/items` | Lista |
| `POST` | `/api/items` | Crea (`{"title", "note"}`) |
| `GET` | `/api/items/{id}` | Singolo |
| `PUT` | `/api/items/{id}` | Modifica (`{"title", "note"}`) |
| `DELETE` | `/api/items/{id}` | Elimina |

La migrazione viene eseguita automaticamente dall'entrypoint; la tabella `items` e le tabelle di default di Laravel (users, cache, sessions) vengono create al primo avvio.

## Comandi

```bash
# avviare (compila l'immagine — la prima volta richiede rete per composer)
docker compose up --build

# aprire l'interfaccia web
# http://localhost:8020

# esempi con curl
curl http://localhost:8020/api/items
curl -X POST -H "Content-Type: application/json" -d '{"title":"Ciao","note":"da curl"}' http://localhost:8020/api/items
curl http://localhost:8020/api/items/1
curl -X PUT -H "Content-Type: application/json" -d '{"title":"Aggiornato","note":"ok"}' http://localhost:8020/api/items/1
curl -X DELETE http://localhost:8020/api/items/1

# log dal container
docker compose logs -f

# artisan (esempio: creare un item da cli)
docker compose exec app php artisan tinker
# > App\Models\Item::create(["title" => "CLI", "note" => "test"]);

# fermare tutto
docker compose down

# fermare ed eliminare anche i volumi (perde i dati)
docker compose down -v
```