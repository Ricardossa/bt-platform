# Motor de Atualização OTA (Over-The-Air)

## Funcionamento
1. A Master gera um pacote ZIP (via `PackageController`).
2. O manifesto é gerado com um Hash SHA256 para integridade.
3. O cliente (Enterprise) consulta `/api/v1/updates_check.php` em intervalos regulares.
4. Ao detectar nova versão, o cliente baixa o ZIP e aplica de forma atômica.

## Segurança
- Validação de assinatura digital do pacote.
- Rollback automático em caso de falha na extração.
- Preservação de dados sensíveis (`banco.db` e `uploads/`).
