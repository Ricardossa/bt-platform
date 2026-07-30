# Arquitetura Master-Node (BT Queue 2.0)

## Modelo Híbrido
O ecossistema utiliza uma arquitetura de **Edge Computing**:
1. **Master (Cloud)**: Centraliza a inteligência de negócios e segurança.
2. **Enterprise (Edge)**: Roda localmente no cliente, operando mesmo sem internet.

## Fluxo de Sincronização
A Enterprise envia dados via `SyncService` para o endpoint `/api/v1/sync.php` da Master. O protocolo é JSON sobre HTTP/S.
- **Batimento (Pulse)**: A cada 5 minutos.
- **Eventos de Senha**: Sincronização imediata (Real-time).
