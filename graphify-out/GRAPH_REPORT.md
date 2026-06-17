# Graph Report - .  (2026-06-17)

## Corpus Check
- Corpus is ~12,943 words - fits in a single context window. You may not need a graph.

## Summary
- 478 nodes · 688 edges · 68 communities (61 shown, 7 thin omitted)
- Extraction: 98% EXTRACTED · 2% INFERRED · 0% AMBIGUOUS · INFERRED: 13 edges (avg confidence: 0.8)
- Token cost: 0 input · 0 output

## Community Hubs (Navigation)
- [[_COMMUNITY_DataTables Layer|DataTables Layer]]
- [[_COMMUNITY_Boleto & Cartera Models|Boleto & Cartera Models]]
- [[_COMMUNITY_Admin Controllers & Audit|Admin Controllers & Audit]]
- [[_COMMUNITY_Policies & Authorization|Policies & Authorization]]
- [[_COMMUNITY_API Transformers|API Transformers]]
- [[_COMMUNITY_Business Services|Business Services]]
- [[_COMMUNITY_Orders & Email (Brevo)|Orders & Email (Brevo)]]
- [[_COMMUNITY_Carteras CRUD|Carteras CRUD]]
- [[_COMMUNITY_Form Requests Validation|Form Requests Validation]]
- [[_COMMUNITY_DB Seeders & Menu|DB Seeders & Menu]]
- [[_COMMUNITY_API Presenters|API Presenters]]
- [[_COMMUNITY_Reports & Export|Reports & Export]]
- [[_COMMUNITY_Sorteos CRUD Controller|Sorteos CRUD Controller]]
- [[_COMMUNITY_Service Providers|Service Providers]]
- [[_COMMUNITY_Module Manifest|Module Manifest]]
- [[_COMMUNITY_API Sorteos Controller|API Sorteos Controller]]
- [[_COMMUNITY_ClubPago Payment Service|ClubPago Payment Service]]
- [[_COMMUNITY_PDF Ticket Generation|PDF Ticket Generation]]
- [[_COMMUNITY_Brevo Mail Service|Brevo Mail Service]]
- [[_COMMUNITY_Report Service|Report Service]]
- [[_COMMUNITY_Community 20|Community 20]]
- [[_COMMUNITY_Community 21|Community 21]]
- [[_COMMUNITY_Community 22|Community 22]]
- [[_COMMUNITY_Community 23|Community 23]]
- [[_COMMUNITY_Community 24|Community 24]]
- [[_COMMUNITY_Community 25|Community 25]]
- [[_COMMUNITY_Community 26|Community 26]]
- [[_COMMUNITY_Community 27|Community 27]]
- [[_COMMUNITY_Community 28|Community 28]]
- [[_COMMUNITY_Community 29|Community 29]]

## God Nodes (most connected - your core abstractions)
1. `OrdersController` - 14 edges
2. `Response` - 14 edges
3. `OrderRequest` - 12 edges
4. `ReportsController` - 12 edges
5. `SorteosController` - 11 edges
6. `Order` - 11 edges
7. `CarterasController` - 10 edges
8. `Order` - 10 edges
9. `Sorteo` - 10 edges
10. `SorteoRequest` - 9 edges

## Surprising Connections (you probably didn't know these)
- `CarterasController` --inherits--> `BaseController`  [EXTRACTED]
  Corals/modules/Sorteos/Http/Controllers/CarterasController.php →   _Bridges community 2 → community 7_
- `OrdersController` --inherits--> `BaseController`  [EXTRACTED]
  Corals/modules/Sorteos/Http/Controllers/OrdersController.php →   _Bridges community 2 → community 6_
- `ReportsController` --inherits--> `BaseController`  [EXTRACTED]
  Corals/modules/Sorteos/Http/Controllers/ReportsController.php →   _Bridges community 2 → community 11_
- `SorteosController` --inherits--> `BaseController`  [EXTRACTED]
  Corals/modules/Sorteos/Http/Controllers/SorteosController.php →   _Bridges community 2 → community 12_
- `OrderPolicy` --inherits--> `BasePolicy`  [EXTRACTED]
  Corals/modules/Sorteos/Policies/OrderPolicy.php →   _Bridges community 3 → community 21_

## Import Cycles
- None detected.

## Communities (68 total, 7 thin omitted)

### Community 0 - "DataTables Layer"
Cohesion: 0.06
Nodes (12): BaseDataTable, Builder, Boleto, Cartera, Order, Sorteo, AuditDataTable, BoletosDataTable (+4 more)

### Community 1 - "Boleto & Cartera Models"
Cohesion: 0.09
Nodes (8): BaseModel, LogsActivity, Boleto, Cartera, Order, OrderItem, Sorteo, PresentableTrait

### Community 2 - "Admin Controllers & Audit"
Cohesion: 0.13
Nodes (12): AuditDataTable, BaseController, BoletoRequest, BoletosDataTable, AuditController, BoletosController, PaymentsController, Request (+4 more)

### Community 3 - "Policies & Authorization"
Cohesion: 0.15
Nodes (10): BasePolicy, Boleto, User, Cartera, User, Sorteo, User, BoletoPolicy (+2 more)

### Community 4 - "API Transformers"
Cohesion: 0.13
Nodes (9): BaseTransformer, Boleto, Cartera, Order, Sorteo, BoletoTransformer, CarteraTransformer, OrderTransformer (+1 more)

### Community 5 - "Business Services"
Cohesion: 0.17
Nodes (9): BaseServiceClass, Cartera, FormRequest, FormRequest, Order, BoletoService, CarteraService, OrderService (+1 more)

### Community 6 - "Orders & Email (Brevo)"
Cohesion: 0.23
Nodes (8): BrevoMailService, OrdersController, BoletoDigitalService, Order, OrderRequest, OrdersDataTable, OrderService, Response

### Community 7 - "Carteras CRUD"
Cohesion: 0.21
Nodes (7): CarteraRequest, CarterasDataTable, CarteraService, CarterasController, Cartera, Cartera, CarteraObserver

### Community 8 - "Form Requests Validation"
Cohesion: 0.15
Nodes (5): BaseRequest, BoletoRequest, CarteraRequest, OrderRequest, SorteoRequest

### Community 9 - "DB Seeders & Menu"
Cohesion: 0.15
Nodes (6): Seeder, SorteosDatabaseSeeder, SorteosMenuDatabaseSeeder, SorteosPermissionsDatabaseSeeder, SorteosRolesDatabaseSeeder, SorteosSettingsDatabaseSeeder

### Community 10 - "API Presenters"
Cohesion: 0.17
Nodes (6): SorteoPresenter, FractalPresenter, BoletoPresenter, CarteraPresenter, OrderPresenter, SorteoPresenter

### Community 11 - "Reports & Export"
Cohesion: 0.25
Nodes (5): ReportsController, Carbon, Collection, Request, StreamedResponse

### Community 12 - "Sorteos CRUD Controller"
Cohesion: 0.30
Nodes (5): SorteosController, Sorteo, SorteoRequest, SorteosDataTable, SorteoService

### Community 13 - "Service Providers"
Cohesion: 0.19
Nodes (4): SorteosAuthServiceProvider, SorteosObserverServiceProvider, SorteosRouteServiceProvider, ServiceProvider

### Community 14 - "Module Manifest"
Cohesion: 0.14
Nodes (13): author, autoload, code, description, folder, icon, load_order, name (+5 more)

### Community 15 - "API Sorteos Controller"
Cohesion: 0.31
Nodes (6): SorteosController, APIBaseController, Sorteo, SorteoRequest, SorteosDataTable, SorteoService

### Community 16 - "ClubPago Payment Service"
Cohesion: 0.24
Nodes (3): Order, Request, ClubPagoService

### Community 17 - "PDF Ticket Generation"
Cohesion: 0.42
Nodes (3): Boleto, Order, BoletoDigitalService

### Community 18 - "Brevo Mail Service"
Cohesion: 0.36
Nodes (3): BoletoDigitalService, Order, BrevoMailService

### Community 19 - "Report Service"
Cohesion: 0.24
Nodes (4): Carbon, Collection, LengthAwarePaginator, ReportService

### Community 21 - "Community 21"
Cohesion: 0.50
Nodes (3): Order, User, OrderPolicy

### Community 22 - "Community 22"
Cohesion: 0.43
Nodes (3): Blueprint, Migration, SorteosTables

### Community 24 - "Community 24"
Cohesion: 0.60
Nodes (3): SorteoTransformer, APIBaseTransformer, Sorteo

## Knowledge Gaps
- **15 isolated node(s):** `Collection`, `LengthAwarePaginator`, `code`, `name`, `author` (+10 more)
  These have ≤1 connection - possible missing edges or undocumented components.
- **7 thin communities (<3 nodes) omitted from report** — run `graphify query` to explore isolated nodes.

## Suggested Questions
_Questions this graph is uniquely positioned to answer:_

- **Why does `Response` connect `Orders & Email (Brevo)` to `Admin Controllers & Audit`, `Reports & Export`, `Sorteos CRUD Controller`, `Carteras CRUD`?**
  _High betweenness centrality (0.006) - this node is a cross-community bridge._
- **Why does `OrdersController` connect `Orders & Email (Brevo)` to `Admin Controllers & Audit`?**
  _High betweenness centrality (0.006) - this node is a cross-community bridge._
- **Why does `ReportsController` connect `Reports & Export` to `Admin Controllers & Audit`?**
  _High betweenness centrality (0.006) - this node is a cross-community bridge._
- **Are the 13 inferred relationships involving `Response` (e.g. with `.download()` and `.destroy()`) actually correct?**
  _`Response` has 13 INFERRED edges - model-reasoned connections that need verification._
- **What connects `Collection`, `LengthAwarePaginator`, `code` to the rest of the system?**
  _15 weakly-connected nodes found - possible documentation gaps or missing edges._
- **Should `DataTables Layer` be split into smaller, more focused modules?**
  _Cohesion score 0.05893719806763285 - nodes in this community are weakly interconnected._
- **Should `Boleto & Cartera Models` be split into smaller, more focused modules?**
  _Cohesion score 0.09243697478991597 - nodes in this community are weakly interconnected._