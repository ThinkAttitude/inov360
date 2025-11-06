````markdown
# RH360

Painel web para gestão operacional de uma empresa, com backoffice em PHP/MySQL e um dashboard moderno em JavaScript.

---

## 1. Visão geral

Este projeto é uma aplicação web que permite:

- Autenticação de utilizadores (login / logout)
- Dashboard com navegação tipo SPA (via `#hash`) dentro da área autenticada
- Consulta de horários
- Marcação direta de férias/ausências
- Consulta/gestão de pedidos e fichas de colaboradores
- Integração com API REST em PHP (JSON + uploads de ficheiros)

---

## 2. Tecnologias principais

**Frontend**

- HTML5, CSS3 (layouts modernos, grid/flex)
- JavaScript ES Modules (`import`/`export`)
- Navegação interna por `hash` (`router.js`)

**Backend**

- PHP (estilo clássico, sem framework)
- Endpoints REST em `/api/...`
- Sessões PHP (`$_SESSION['user']`) para autenticação
- Upload de ficheiros (PDF/JPG/PNG) para modulos especificos

**Base de Dados**

- MySQL

**Infraestrutura**

- **Desenvolvimento/Teste:** Docker + Docker Compose  
- **Produção:** Servidor Linux com PHP + MySQL (sem Docker)

---

## 3. Como correr em desenvolvimento (Docker)

Requisitos:

* [Docker](https://www.docker.com/)
* [Docker Compose](https://docs.docker.com/compose/)

No diretório raiz, onde está o `docker-compose.yml`, encontra-se fora do repositorio Git por agora.

```bash
docker-compose up --d
```

Depois:

* Aplicação: [http://localhost:8080](http://localhost:8080)
* phpMyAdmin: [http://localhost:8081](http://localhost:8081)

Os volumes:

* Código PHP / frontend: montado em `/var/www/html`
* Base de dados MySQL: volume `db_data`

> Nota: para uploads funcionarem em desenvolvimento é preciso garantir permissões de escrita no diretório `uploads/` dentro do container (por exemplo, dar `chown`/`chmod` apropriado).

---

## 4. Como funciona a navegação

### 4.1. Fora do dashboard

* Páginas como `page_login.php` usam navegação “normal” de browser (full page load).
* O login cria `$_SESSION['user']` e redireciona para `dashboard.php`.

### 4.2. Dentro do dashboard

* `dashboard.php` tem:

  * Sidebar fixa (links com `href="#horarios"`, `href="#pedidos_ferias"`, etc.)
  * `<main id="main-content">` vazio; o conteúdo é carregado via JS
  * `window.CURRENT_USER` é preenchido com o utilizador da sessão (PHP → JS)

* `router.js`:

  * Lê `location.hash` (ex: `#horarios`)
  * Procura o caminho correspondente em `Routes`
  * Faz `fetch` do módulo HTML (ex: `/page/modules/horarios.html`)
  * Insere o HTML em `<main id="main-content">`
  * Opcionalmente chama o JS do módulo (ex: `horarios.js`) para iniciar lógica específica

* `dashboard.js`:

  * Calcula que cards/menu mostrar com base nas permissões do utilizador
  * Gera entradas dinâmicas na sidebar (com `href="#..."`)
  * Preenche “welcome cards” no dashboard inicial
  * Trata do botão de logout (redirect para endpoint PHP de logout)

---

## 5. Autenticação e sessão

* O backend usa sessões PHP (`session_start()`).

* Após login, `$_SESSION['user']` contém os dados base do utilizador, incluindo permissões.

* No `dashboard.php`, estes dados são expostos à camada JS via:

  ```html
  <script>
    window.CURRENT_USER = <?= json_encode($_SESSION['user'], ...) ?>;
  </script>
  ```

* O logout é feito apontando o browser para o endpoint de logout, que destrói a sessão e redireciona para a página de login.

---

## 6. Endpoints principais (exemplos)

Alguns endpoints típicos (os nomes exatos podem variar de acordo com a implementação):

* `api/auth/login.php` – login (POST, JSON)
* `api/auth/logout.php` – logout (GET, redirect)
* `api/calendar/get_month.php` – devolve horas / férias por dia para um mês
* `api/leaves/direct_leave.php` – marcação direta de férias/ausências com upload
* `api/collabs_list.php` – lista de colaboradores + permissões

As funções JS em `api.js` escondem os detalhes de `fetch` e devolvem objetos JS.

---

## 7. Notas finais

* A área autenticada usa uma abordagem “SPA-like” apenas **dentro** do dashboard.
* O resto do site continua a usar navegação clássica PHP (cada página é renderizada no servidor).
* O código está organizado por módulos (`horarios`, `marcacao_direta`, etc.) para separar HTML, CSS e JS específicos de cada página.

```
```
