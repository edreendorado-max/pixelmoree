# Pixmoree Project Diagrams

These diagrams describe the current Laravel MVC application and manual payment workflow. The database schema is defined by the Laravel migrations in database/migrations.

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

## 3. Client and admin transaction flow

```mermaid
flowchart TD
    Client[Signed-in client] --> Choice{Choose transaction}
    Choice -->|Buy NFT with GCash| Checkout[Select one available Item ID]
    Checkout --> Proof[Submit transaction reference and receipt]
    Proof --> Pending[Payment order pending review; item reserved]
    Pending --> Review{Admin checks GCash directly}
    Review -->|Approve| Transfer[Transfer selected item to buyer]
    Transfer --> Credit[Credit creator Pixel Coins]
    Review -->|Reject| Release[Reject order and release item]
    Choice -->|Buy with Pixel Coins| CoinBuy[Debit buyer and credit creator in one DB transaction]
    CoinBuy --> CoinTransfer[Transfer selected Item ID]
    Choice -->|Top up coins| Topup[Submit GCash receipt]
    Topup --> TopupReview[Admin approves or rejects top-up]
    TopupReview -->|Approve| CoinCredit[Credit top-up coins]
    Choice -->|Withdraw coins| Withdraw[Reserve coins and create payout request]
    Withdraw --> Payout[Admin sends GCash and records payout reference]
    Payout -->|Paid| Close[Mark payout paid and clear reservation]
    Payout -->|Rejected| Refund[Refund reserved coins]
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
    USERS ||--o{ PIXEL_COIN_TRANSACTIONS : has
    PAYMENT_ORDERS o|--o{ PIXEL_COIN_TRANSACTIONS : recorded_as
    ARTWORK_ITEMS o|--o{ PIXEL_COIN_TRANSACTIONS : references
    PAYMENT_ORDERS ||--o| OWNERSHIP_TRANSFERS : may_create
    USERS ||--o{ OWNERSHIP_TRANSFERS : receives

    USERS {
        bigint id PK
        string account_id UK
        string email UK
        string role
        bigint pixel_coin_balance
        bigint pixel_coin_reserved
        string gcash_number
        string gcash_qr_path
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
        bigint coin_amount
        bigint fee_amount_minor
        string status
        string transaction_reference
    }
    PIXEL_COIN_TRANSACTIONS {
        uuid transaction_id PK
        bigint user_id FK
        uuid payment_order_id
        string artwork_id FK
        uuid artwork_item_id
        string direction
        bigint amount
        bigint balance_after
    }
    OWNERSHIP_TRANSFERS {
        bigint id PK
        uuid item_id
        uuid purchase_id UK
        bigint from_user_id FK
        bigint to_user_id FK
    }
```

Edition item IDs are unique. A pending GCash order reserves its selected item, and approval transfers that item in a database transaction. Coin balances and ledger amounts are stored in hundredths of a Pixel Coin.

Payment-order and ledger Item ID fields are application-validated UUID references; the current schema does not define foreign-key constraints for those item references. User, artwork, and purchase relationships marked FK are enforced by database constraints.
