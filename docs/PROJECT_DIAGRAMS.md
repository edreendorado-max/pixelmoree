# Pixmoree Project Diagrams

These diagrams describe the current Laravel MVC application and its simulation-only purchase and withdrawal workflow. Laravel migrations define the current schema.

## 1. MVC request and data flow

```mermaid
flowchart LR
    Browser[Client or admin browser] --> Routes[web.php routes]
    Routes --> Middleware[Authentication and role checks]
    Middleware --> Controller[Laravel controller]
    Controller --> Models[Eloquent models and transaction services]
    Models --> Database[(MySQL database)]
    Controller --> View[Blade view]
    View --> Browser
    Controller --> PrivateFiles[Private local file storage]
```

## 2. Account registration and sign-in

```mermaid
sequenceDiagram
    actor Visitor
    participant Web as Laravel routes and controller
    participant DB as MySQL
    Visitor->>Web: Submit registration form
    Web->>Web: Validate fields and hash password
    Web->>DB: Save user and welcome notification
    Web-->>Visitor: Sign in and open account area immediately
    Visitor->>Web: Sign in later with email or username and password
    Web->>DB: Authenticate credentials
```

## 3. Simulated transaction flow

```mermaid
flowchart TD
    Buyer[Signed-in buyer] --> Checkout[Choose an available edition]
    Checkout --> Purchase[Create approved simulated PHP purchase]
    Purchase --> Transfer[Transfer one edition item to buyer]
    Transfer --> Receipt[Issue receipt and notify buyer and seller]
    Purchase --> Earnings[Credit creator simulated PHP earnings]
    Creator[Creator] --> Withdraw[Request simulated withdrawal up to balance]
    Withdraw --> Debit[Debit simulated earnings and create payout record]
    Debit --> PayoutReceipt[Issue withdrawal receipt; no cash is sent]
    Admin[Admin Studio] --> Records[Inspect purchase and withdrawal records]
    Admin --> Review[Moderate artwork and resale evidence]
```

## 4. Relational database diagram (ERD)

```mermaid
erDiagram
    USERS ||--o{ ARTWORKS : creates
    ARTWORKS ||--|{ ARTWORK_ITEMS : has
    USERS o|--o{ ARTWORK_ITEMS : owns
    USERS ||--o{ PAYMENT_ORDERS : submits
    USERS o|--o{ PAYMENT_ORDERS : sells
    ARTWORKS o|--o{ PAYMENT_ORDERS : concerns
    ARTWORK_ITEMS o|--o{ PAYMENT_ORDERS : item
    USERS ||--o{ CASH_LEDGER_ENTRIES : has
    PAYMENT_ORDERS ||--o{ CASH_LEDGER_ENTRIES : records
    PAYMENT_ORDERS ||--o| OWNERSHIP_TRANSFERS : may_create
    USERS ||--o{ OWNERSHIP_TRANSFERS : receives

    USERS {
        bigint id PK
        string account_id UK
        string email UK
        string role
        bigint cash_balance_minor
    }
    ARTWORKS {
        string id PK
        string product_id UK
        bigint user_id FK
        unsigned item_count
        string status
    }
    ARTWORK_ITEMS {
        uuid item_id PK
        string artwork_id FK
        unsigned edition_number
        bigint owner_id FK
        uuid acquired_purchase_id UK
        uuid reserved_purchase_id UK
        string status
    }
    PAYMENT_ORDERS {
        uuid purchase_id PK
        bigint buyer_id FK
        bigint seller_id FK
        string artwork_id FK
        uuid item_id
        string type
        bigint amount_minor
        string currency
        string payment_method
        string status
    }
    CASH_LEDGER_ENTRIES {
        bigint id PK
        bigint user_id FK
        uuid purchase_id FK
        string type
        string direction
        bigint amount_minor
        bigint balance_after_minor
    }
    OWNERSHIP_TRANSFERS {
        bigint id PK
        uuid item_id
        uuid purchase_id UK
        bigint from_user_id FK
        bigint to_user_id FK
    }
```

Edition item IDs are unique. Each simulated purchase transfers its selected item and records the purchase atomically. Cash balances and ledger amounts are stored as integer centavos and represent in-app demo values only. A purchase has one ledger entry for the creator; a simulated withdrawal has one debit entry for the creator. Historical migrations contain the removed Pixel Coin schema, and the cleanup migration drops its table and balance fields.

Payment-order and ownership-transfer item ID fields are application-validated UUID references; the current schema does not define foreign-key constraints for those item references. User, artwork, purchase, and cash-ledger relationships marked FK are enforced by database constraints.
