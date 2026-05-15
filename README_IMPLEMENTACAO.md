Desafio Técnico Laravel
Sobre o projeto

Este projeto foi desenvolvido como solução para o desafio técnico utilizando Laravel como framework principal.

Minha stack principal hoje é Node.js com TypeScript. Já tive contato com Laravel anteriormente em contextos acadêmicos, mas nunca havia desenvolvido algo mais completo utilizando o ecossistema do framework. Por conta disso, durante o desenvolvimento deste teste precisei estudar novamente diversos conceitos do Laravel, principalmente relacionados a filas, Eloquent, Filament, organização da aplicação e estratégias de performance.

O objetivo principal durante o desenvolvimento foi construir uma aplicação organizada, performática e que simulasse decisões que normalmente seriam tomadas em um ambiente real de produção.

Mesmo sendo um projeto relativamente pequeno, tentei manter uma boa separação de responsabilidades e evitar acoplamentos desnecessários. A ideia foi deixar o sistema simples de entender e ao mesmo tempo preparado para crescer futuramente.

Estrutura e arquitetura

A aplicação foi construída pensando em conceitos de SOLID e Clean Architecture, mesmo sem criar uma estrutura extremamente complexa de camadas.

A responsabilidade da interface ficou concentrada no Filament.

A responsabilidade do processamento pesado ficou isolada nas filas do Laravel.

A responsabilidade da geração do relatório ficou separada dentro de uma Job específica.

Os Models ficaram responsáveis apenas pelos relacionamentos e entidades.

Isso ajudou bastante a manter o código mais organizado e previsível.

Durante o desenvolvimento tentei evitar que regras importantes ficassem espalhadas pela aplicação.

Funcionalidade principal

A principal funcionalidade implementada foi a geração de um relatório CSV consolidando dados dos alunos.

O relatório contém:

Nome
E-mail
Data de nascimento
CPF
RG
Endereço
CEP
Quantidade de aprovações
Quantidade de reprovações
Faixa escolar
Escola mais recente
Estratégia de performance

Inicialmente considerei fazer uma consulta muito grande utilizando múltiplos JOINs e GROUP BY.

Porém, durante os testes, percebi que a consulta começava a ficar pesada e menos escalável conforme o volume de dados aumentava.

A solução final foi refatorada pensando em menor consumo de memória e processamento em lotes.

A exportação utiliza chunking:

->chunk(1000)

Isso faz com que o Laravel processe os registros em pequenos grupos ao invés de carregar tudo na memória de uma vez.

Essa abordagem reduz bastante o consumo de memória e deixa a exportação mais estável.

Uso de filas

A geração do relatório foi implementada utilizando Queues do Laravel.

A principal ideia aqui foi evitar que a aplicação travasse durante o processamento do CSV.

Quando o usuário solicita a exportação, a interface apenas dispara uma Job para a fila.

O processamento acontece em background através do worker.

Isso evita:

timeout
travamento da aplicação
alto consumo de memória na request principal
lentidão na interface

A Job responsável pela exportação é:

GenerateUsersExportJob

Ela é responsável por:

buscar os dados
processar os relacionamentos
gerar o CSV
salvar o arquivo
atualizar o cache do último relatório gerado
Experiência do usuário

Mesmo sendo um teste técnico, tentei me preocupar também com a experiência do usuário.

A exportação funciona de forma assíncrona, permitindo que o usuário continue utilizando a aplicação enquanto o relatório é gerado.

A interface possui:

botão para gerar relatório
notificação informando que o processamento foi iniciado
botão para baixar o último arquivo gerado
Tecnologias utilizadas
PHP 8.3
Laravel 12
Filament
MySQL
Docker
Como executar o projeto

1. Clonar o repositório
   git clone <repo>
2. Subir os containers
   docker compose up -d
3. Instalar dependências
   docker compose exec app composer install
4. Configurar ambiente
   cp .env.example .env
5. Gerar APP_KEY
   docker compose exec app php artisan key:generate
6. Rodar migrations
   docker compose exec app php artisan migrate
7. Criar link do storage
   docker compose exec app php artisan storage:link

Esse passo é importante para permitir o download correto dos arquivos CSV.

Worker da fila

Para que a exportação funcione corretamente é necessário deixar o worker da fila executando.

Rodar em outro terminal:

docker compose exec app php artisan queue:work

Sem isso, as Jobs não serão processadas.

Acesso da aplicação

Aplicação:

http://localhost

Painel administrativo:

http://localhost/admin
Decisões técnicas

Optei por CSV porque é um formato simples, leve e extremamente rápido de gerar.

Também considerei que para o contexto do teste ele fazia mais sentido do que utilizar bibliotecas mais pesadas para XLSX.

A utilização de filas foi uma decisão importante principalmente pensando em escalabilidade e experiência do usuário.

A utilização de chunking foi essencial para evitar problemas de memória durante o processamento.

Aprendizados

Como minha experiência principal está mais voltada ao ecossistema Node.js, esse projeto foi uma oportunidade muito boa para aprofundar conhecimentos no Laravel.

Durante o desenvolvimento precisei estudar mais sobre:

lifecycle do framework
Filament
Queues
Workers
Eloquent ORM
otimização de consultas
integração entre backend e interface administrativa

Foi interessante perceber como vários conceitos arquiteturais que utilizo em Node.js também se aplicam muito bem no Laravel.

Considerações finais

O principal foco deste projeto foi construir uma solução simples, organizada e performática.

Mesmo sendo um teste técnico, tentei desenvolver pensando em problemas reais que normalmente aparecem em ambientes de produção, principalmente relacionados a processamento pesado, filas e experiência do usuário.

Além disso, o projeto foi uma ótima oportunidade para aprofundar conhecimentos no ecossistema Laravel e entender melhor como o framework trabalha internamente.
