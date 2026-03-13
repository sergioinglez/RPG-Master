# 🎲 RPG Master Toolkit - WordPress Plugin + Theme

## Visão Geral

Ferramenta completa de apoio para **mestres e jogadores de RPG (D&D 5e)** para WordPress. O sistema é composto por um **Plugin** (lógica, banco de dados, API) e um **Tema** (interfaces visuais).

---

## 📦 Estrutura do Projeto

```
📁 rpg-master-toolkit/          ← PLUGIN (wp-content/plugins/)
│
├── rpg-master-toolkit.php       # Arquivo principal do plugin
├── includes/
│   ├── Roles.php                # Sistema de roles e capabilities
│   ├── PostTypes.php            # Custom Post Types + Meta Boxes
│   ├── DnD5e_Rules.php          # Motor de regras D&D 5e
│   ├── Character_Manager.php    # CRUD de personagens
│   ├── Adventure_Manager.php    # Gestão de aventuras
│   ├── Session_Manager.php      # Sessão em tempo real
│   ├── REST_API.php             # Endpoints REST API
│   └── Admin_Pages.php          # Páginas do WP-Admin
├── assets/
│   ├── css/
│   │   ├── admin.css            # Estilos do admin
│   │   └── frontend.css         # Estilos compartilhados frontend
│   └── js/
│       ├── admin.js             # Scripts do admin
│       └── frontend.js          # API client + helpers D&D

📁 rpg-master-theme/             ← TEMA (wp-content/themes/)
│
├── style.css                    # Stylesheet do tema
├── functions.php                # Setup do tema, templates, redirects
├── index.php                    # Página inicial (portal)
├── header.php                   # Header padrão
├── footer.php                   # Footer padrão
├── template-dm-dashboard.php    # 🎲 Interface do Mestre
├── template-player-dashboard.php # ⚔️ Interface do Jogador
├── template-display.php         # 📺 Tela de Exibição (TV)
├── assets/
│   ├── css/
│   │   ├── dm-dashboard.css     # Estilos do painel DM
│   │   ├── player-dashboard.css # Estilos do painel do jogador
│   │   └── display.css          # Estilos do display
│   └── js/
│       ├── dm-dashboard.js      # Lógica do painel DM
│       ├── player-dashboard.js  # Lógica do painel do jogador
│       └── display.js           # Lógica do display
```

---

## 🚀 Instalação

### 1. Plugin
1. Copie a pasta `rpg-master-toolkit/` para `wp-content/plugins/`
2. No WP-Admin, vá em **Plugins** → **Ativar** "RPG Master Toolkit"
3. A ativação criará automaticamente:
   - Tabelas no banco de dados (personagens, inventário, conquistas, sessão, controles DM)
   - Capabilities nos roles do WordPress
   - Custom Post Types (Aventuras, Cenas, NPCs, Monstros, Mapas)

### 2. Tema
1. Copie a pasta `rpg-master-theme/` para `wp-content/themes/`
2. No WP-Admin, vá em **Aparência** → **Temas** → Ativar "RPG Master Theme"
3. A ativação criará automaticamente 3 páginas:
   - `/rpg-master/` → Painel do Mestre
   - `/rpg-player/` → Painel do Jogador
   - `/rpg-display/` → Tela de Exibição

### 3. Verificar Permalinks
- Vá em **Configurações** → **Links Permanentes** → Clique "Salvar Alterações" (para flush)

---

## 👥 Sistema de Papéis

| Role WordPress | Papel RPG | Acesso |
|---|---|---|
| **Assinante** | Jogador | Criar personagens, ver ficha, inventário, conquistas |
| **Colaborador** | Jogador | Idem Assinante |
| **Autor** | Mestre (DM) | Tudo do jogador + mestrar aventuras pré-configuradas |
| **Editor** | Mestre (DM) | Idem Autor + gerenciar NPCs/Monstros |
| **Administrador** | Admin | Tudo + configurar aventuras, criar conteúdo, gerenciar settings |

> ⚠️ O registro de jogadores é feito pelo **Admin** via WP-Admin → Usuários → Adicionar Novo

---

## 🎯 Funcionalidades Implementadas (MVP)

### ✅ Plugin
- [x] Tabelas customizadas no banco de dados
- [x] Custom Post Types: Aventuras, Cenas, NPCs, Monstros, Mapas
- [x] Meta boxes completas com todos os campos D&D 5e
- [x] Sistema de Roles & Capabilities mapeado aos roles do WP
- [x] Motor de regras D&D 5e (raças, classes, backgrounds, skills, condições, spell slots)
- [x] CRUD completo de personagens (limite: 5 por jogador)
- [x] Sistema de inventário
- [x] Cálculos automáticos (HP, modificadores, proficiência, XP → Level)
- [x] Gestão de aventuras (cenas ordenadas, NPCs, monstros, mapas)
- [x] Sessão em tempo real (início, pausa, encerramento)
- [x] Controle de cenas (troca de cena → atualiza display)
- [x] Controle do DM sobre jogadores (HP, XP, condições)
- [x] REST API completa (30+ endpoints)
- [x] Tela de exibição via polling (configurável)
- [x] Páginas admin (Painel DM, Sessão Ativa, Jogadores, Configurações)

### ✅ Tema
- [x] 3 templates de página (Mestre, Jogador, Display)
- [x] Criação automática das páginas
- [x] Interface do Mestre: visão geral, sessão, controle de jogadores, NPCs, bestiário
- [x] Interface do Jogador: criação de personagem (3 steps), ficha completa, inventário
- [x] Tela de Exibição: mapa com tokens, combate, NPC, idle screen
- [x] Dice roller flutuante (d4 a d100 + custom)
- [x] Design dark theme com visual RPG
- [x] Verificação de acesso por role
- [x] Redirect de jogadores do WP-Admin para painel
- [x] Ocultar admin bar para jogadores
- [x] Transições de cena na tela de exibição
- [x] Fullscreen automático no display

---

## 🔗 URIs e Endpoints

### Páginas Frontend
| URL | Template | Acesso |
|---|---|---|
| `/rpg-master/` | Painel do Mestre | Autor/Editor/Admin |
| `/rpg-player/` | Painel do Jogador | Todos logados |
| `/rpg-display/` | Tela de Exibição | Público (TV/projetor) |

### REST API (`/wp-json/rmt/v1/`)

#### Personagens
- `GET /characters` — Listar meus personagens
- `POST /characters` — Criar personagem
- `GET /characters/{id}` — Ver personagem
- `PUT /characters/{id}` — Atualizar personagem
- `DELETE /characters/{id}` — Deletar personagem
- `GET /characters/{id}/sheet` — Ficha completa calculada

#### Inventário
- `GET /characters/{id}/inventory` — Listar itens
- `POST /characters/{id}/inventory` — Adicionar item
- `PUT /inventory/{item_id}` — Atualizar item
- `DELETE /inventory/{item_id}` — Remover item

#### Aventuras
- `GET /adventures` — Listar aventuras
- `GET /adventures/{id}` — Detalhes (com cenas, NPCs, monstros, mapas)

#### Sessão
- `POST /session/start` — Iniciar sessão
- `GET /session/status` — Status atual
- `POST /session/scene` — Mudar cena
- `POST /session/npc` — Mostrar NPC no display
- `POST /session/move` — Mover token no mapa
- `POST /session/combat` — Atualizar combate
- `POST /session/pause` — Pausar sessão
- `POST /session/end` — Encerrar sessão

#### Display
- `GET /display` — Dados para tela de exibição (público)

#### Controles do DM
- `GET /dm/characters` — Todos os personagens
- `POST /dm/player/{id}/hp` — Modificar HP
- `POST /dm/player/{id}/xp` — Conceder XP
- `POST /dm/player/{id}/condition` — Aplicar condição
- `DELETE /dm/player/{id}/condition` — Remover condição

#### Regras D&D 5e (público)
- `GET /rules/races` — Raças
- `GET /rules/classes` — Classes
- `GET /rules/backgrounds` — Antecedentes
- `GET /rules/conditions` — Condições
- `GET /rules/skills` — Perícias

---

## 🗄️ Modelo de Dados

### Tabelas Customizadas
| Tabela | Descrição |
|---|---|
| `wp_rmt_characters` | Personagens dos jogadores (atributos, skills, saves, RP) |
| `wp_rmt_inventory` | Itens do inventário (armas, armaduras, poções, etc.) |
| `wp_rmt_achievements` | Conquistas dos personagens |
| `wp_rmt_active_session` | Sessão ativa (cena atual, combate, posições, log) |
| `wp_rmt_dm_player_controls` | Controles do DM sobre jogadores |

### Custom Post Types
| CPT | Descrição |
|---|---|
| `rmt_adventure` | Aventuras |
| `rmt_scene` | Cenas (vinculadas a aventuras) |
| `rmt_npc` | NPCs com diálogos, personalidade, stats |
| `rmt_monster` | Monstros/Inimigos com stat block completo |
| `rmt_map` | Mapas (jogador + DM version) |

### Taxonomias
| Taxonomia | Aplicada a |
|---|---|
| `rmt_scene_type` | Cenas |
| `rmt_creature_type` | Monstros |
| `rmt_location` | Cenas, Mapas, NPCs |

---

## 🔮 Próximos Passos (Roadmap)

### Fase 2 — Funcionalidades Avançadas
- [ ] Sistema de combate completo (iniciativa, turnos, tracker)
- [ ] Gerenciador de spells/magias
- [ ] Subclasses detalhadas com features por nível
- [ ] Sistema de conquistas completo com triggers automáticos
- [ ] Roller de dados integrado com resultados compartilhados
- [ ] Fog of War no mapa
- [ ] Chat em tempo real (ou via SSE)

### Fase 3 — Polimento
- [ ] Multiclasse
- [ ] Drag & drop de tokens no mapa (DM)
- [ ] Exportar/Importar personagens (JSON/PDF)
- [ ] Temas visuais para display (pergaminho, dark, futurista)
- [ ] Músicas ambiente por cena
- [ ] App PWA (Progressive Web App)
- [ ] Notificações push para jogadores

### Melhorias Técnicas
- [ ] WebSocket em vez de polling (melhor performance)
- [ ] Testes unitários (PHPUnit)
- [ ] Internacionalização completa (EN/PT-BR)
- [ ] Cache de queries pesadas
- [ ] Logs de ações persistentes

---

## ⚙️ Configurações

Acesse **WP-Admin → 🎲 RPG Toolkit → ⚙️ Configurações** para:
- Intervalo de polling do display (padrão: 3000ms)
- Tema visual da tela de exibição (dark/light/pergaminho)

---

## 📋 Requisitos

- WordPress 6.0+
- PHP 7.4+
- MySQL 5.7+
- Permalinks habilitados (qualquer estrutura exceto "Simples")

---

## 🐛 Troubleshooting

### Tabelas não criadas
Desative e reative o plugin. As tabelas são criadas no hook de ativação.

### Páginas 404
Vá em Configurações → Links Permanentes → Salvar (flush rewrite rules).

### REST API retornando 403
Verifique se o nonce está sendo enviado. O cookie de autenticação do WordPress precisa estar presente.

### Display não atualiza
Verifique se há uma sessão ativa. O display precisa de uma sessão com status "active".

---

**Versão:** 1.0.0-MVP | **Licença:** GPL v2+ | **Compatível com:** D&D 5e SRD
