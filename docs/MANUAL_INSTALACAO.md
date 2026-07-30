# Manual de Implantação BT Queue 2.0 (Fisíco/Unidade)

Este guia rápido foi preparado para auxiliar na instalação técnica do sistema **BT Queue Enterprise** em farmácias ou unidades de atendimento que possuem IP Público Fixo.

## 1. Topologia de Rede
*   **Servidor Local**: Computador Windows que rodará o motor principal.
*   **Tablet (Totem)**: Dispositivo de autoatendimento.
*   **Smart TV**: Painel de chamadas.
*   **Celular do Cliente**: Acompanhamento via 4G.

---

## 2. Configuração de Portas no Modem (NAT)
Para que o sistema receba pedidos de fora (Mobile/4G) e sincronize com a Master, as seguintes regras devem ser criadas no modem do cliente apontando para o **IP Local do Servidor**:

| Porta WAN | Porta LAN | Protocolo | Alvo | Uso |
| :--- | :--- | :--- | :--- | :--- |
| **8090** | **8090** | TCP | IP_LOCAL | Sistema Principal e Mobile |
| **8001** | **8001** | TCP | IP_LOCAL | Ponte de Impressão (Se usar Tablet) |

---

## 3. Links de Acesso na Unidade

| Módulo | Link Interno (IP Local) | Link Externo (IP Fixo) |
| :--- | :--- | :--- |
| **Dashboard/Admin** | `http://localhost:8090/dashboard.php` | `http://IP_FIXO:8090/dashboard.php` |
| **Totem (Autoatendimento)**| `http://IP_LOCAL:8090/totem.php` | `http://IP_FIXO:8090/totem.php` |
| **Painel de TV** | `http://IP_LOCAL:8090/tv_v2.php` | `http://IP_FIXO:8090/tv_v2.php` |
| **QR Code Mobile** | `http://IP_LOCAL:8090/live_premium/` | `http://IP_FIXO:8090/live_premium/` |

---

## 4. Configuração da Impressora (Bematech)
1.  Instale o driver da Bematech MP-4200.
2.  Vá em **Propriedades da Impressora > Compartilhamento**.
3.  Compartilhe como: **`BT_TICKET`**.
4.  No Painel Enterprise, vá em **Conectividade** e defina o IP da Impressora como `127.0.0.1`.
5.  Execute o arquivo **`Ligar_Impressora_Local.bat`** na raiz do sistema e mantenha a janela aberta.

---

## 5. Checklist de Inicialização (Obrigatório)
- [ ] Rodar `Ligar_Sistema.bat` (Porta 8090).
- [ ] Rodar `Ligar_Impressora_Local.bat` (Porta 8001).
- [ ] Validar se o firewall do Windows 11 permite conexões na porta 8090.
- [ ] Validar se o QR Code do Totem abre no 4G do celular.

---
© 2026 Brandão Tech - Suporte e Engenharia.
