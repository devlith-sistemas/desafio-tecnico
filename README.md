# Teste Técnico - Desenvolvedor PHP / Filament

Bem-vindo ao teste técnico da Devlith! O objetivo deste desafio é avaliar suas habilidades em desenvolvimento backend com Laravel e, principalmente, com o Filament.

> Obs: o desafio foi criado com a arquitetura de pastas padrão do Laravel, mas você pode manejá-la da maneira que preferir.

## O Desafio

O desafio consistirá na exportação de dados em massa a partir de uma tabela do Filament. Todo o código deve ser criado do zero, sem o uso de plugins de terceiros feitos especificamente para o Filament (você pode, e deve, utilizar outros pacotes Laravel, como bibliotecas para manipulação de planilhas) ou o exporter do próprio Filament.

O sistema atual possui um painel administrativo feito em Filament com uma listagem de usuários (entre eles alunos e não alunos) e suas respectivas matrículas. Como existe um grande volume de dados (milhares de registros simulados), o seu objetivo principal é conseguir **gerar um Excel com a relação completa dos alunos e suas matrículas**.

Você deverá criar uma funcionalidade que permita exportar os dados listados como uma planilha Excel no seguinte formato:

| Nome | E-mail | Data de Nascimento | Range de Escolaridade | Escola | CPF | RG | Logradouro | CEP | Aprovações | Reprovações |
| Aluno 1 | aluno@gmail.com | 2000-01-01 | 2020-2024 | Escola 1 | 111.111.111-11 | 11.111.111-1 | Endereco | 99999-999 | 3 | 4 |

Onde:
- Range de escolaridade = {ano da primeira matrícula}-{ano da última matrícula}
- CPF, CEP e RG devem ser formatados com máscara
- Aprovações e Reprovações = quantidade de matrículas com status aprovado e reprovado
- Escola = nome da escola com a matrícula mais recente

### Critérios de Avaliação

O seu teste será avaliado com base nos seguintes critérios:

1. **Performance**: Como você lida com a exportação de uma grande quantidade de dados com Laravel.
2. **Qualidade do Código**: O código está limpo, bem estruturado e segue os padrões do Laravel/Filament? A arquitetura da solução faz sentido?
3. **UX (Experiência do Usuário)**: Como o download será feito? A interface e o fluxo de exportação são intuitivos e amigáveis para o usuário final, considerando que a exportação pode levar algum tempo?

## Como Começar

Siga os passos abaixo para preparar o seu ambiente de desenvolvimento:

1. **Faça um Fork ou Clone o Repositório**
2. **Configuração de Ambiente clonando o .env.example**
3. **Inicie o Ambiente com Docker:** `docker compose up`
4. **Instale as Dependências:** `docker compose exec app composer install`
5. **Execute as Migrations e Seeders:** `docker compose exec app php artisan migrate --seed`
6. **Inicie o Desafio:**
   Acesse o painel do Filament (ex: `http://localhost/admin`), explore o código existente e crie sua Action na tela de "Alunos".
   
> Obs: o acesso é 'admin@admin.com' e a senha é 'admin'.

## Entrega

Após finalizar o desenvolvimento:
- Certifique-se de que não haja lixo de código ou arquivos não utilizados.
- Atualize este `README.md` (ou crie um `NOTES.md`) com as instruções de uso da sua Action.
    - Neste arquivo, caso queira, você pode explicar brevemente as decisões técnicas que você tomou para atingir os critérios de avaliação, principalmente em relação à performance e UX.
- Adicione o usuário `devlith-sistemas` ao repositório.
- Qualquer dúvida, estamos a disposição.

### Boa sorte! Estamos ansiosos para ver a sua solução.
