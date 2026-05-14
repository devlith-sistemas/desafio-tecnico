# Exportação de Alunos para Excel

Este documento descreve a funcionalidade implementada para o desafio técnico da Devlith. O objetivo foi criar uma exportação em massa de alunos para Excel a partir da tela de **Alunos** do Filament, sem usar o Exporter nativo do Filament nem plugins específicos de exportação para Filament.

A solução foi pensada para um cenário real com muitos registros. Por isso, a exportação não é executada durante a requisição HTTP do usuário. Em vez disso, o sistema cria uma solicitação de exportação, envia um job para a fila, gera o arquivo em segundo plano e avisa o usuário quando a planilha estiver pronta.

## Como usar

1. Execute as migrations:

```bash
docker compose exec app php artisan migrate
```

2. Mantenha um worker de fila rodando:

```bash
docker compose exec app php artisan queue:work --timeout=0 --tries=1
```

3. Acesse o painel administrativo em `/admin`.
4. Abra o recurso **Alunos**.
5. Clique em **Exportar Excel**.
6. Continue usando o sistema normalmente enquanto o arquivo é gerado.
7. Quando o processamento terminar, o Filament exibirá uma notificação com a ação **Baixar Excel**.
8. A ação **Últimas exportações** mostra as solicitações recentes, seus status, quantidade de linhas processadas e link para baixar os arquivos concluídos.

## Formato da planilha

A planilha gerada contém as seguintes colunas:

| Nome | E-mail | Data de Nascimento | Faixa de Escolaridade | Escola | CPF | RG | Logradouro | CEP | Aprovações | Reprovações |

As regras de preenchimento seguem o enunciado do desafio:

- **Faixa de Escolaridade**: menor e maior `ano_letivo` encontrados nas matrículas do aluno.
- **Escola**: nome da escola da matrícula mais recente do aluno.
- **CPF, RG e CEP**: valores formatados com máscara.
- **Aprovações e Reprovações**: quantidade de matrículas com `resultado_final` aprovado ou reprovado.
- Apenas usuários com matrícula entram na exportação, respeitando a ideia da listagem de alunos.

## Fluxo da funcionalidade

1. O usuário clica em **Exportar Excel** na tela de alunos.
2. A action do Filament chama `RequestStudentsExport`.
3. `RequestStudentsExport` cria um registro em `student_exports` com status `Pendente`.
4. O job `ExportStudentsJob` é despachado para a fila configurada.
5. O job marca a exportação como `Processando`.
6. `StudentsSpreadsheetExporter` gera a planilha XLSX em streaming.
7. O arquivo final é salvo no disk configurado do Laravel.
8. O registro em `student_exports` é atualizado como `Concluída`.
9. `StudentExportNotifier` envia uma notificação persistente para o usuário com o botão **Baixar Excel**.
10. O download passa por uma rota autenticada que garante que apenas o usuário que solicitou a exportação consiga baixar o arquivo.

## Principais arquivos criados

- `app/Actions/Students/RequestStudentsExport.php`: centraliza a criação da solicitação de exportação e o despacho do job.
- `app/Jobs/ExportStudentsJob.php`: executa a exportação em background via fila.
- `app/Exports/Students/StudentExportRowQuery.php`: contém a consulta otimizada dos dados exportados.
- `app/Exports/Students/StudentsSpreadsheetExporter.php`: escreve a planilha XLSX em streaming.
- `app/Exports/Students/StudentExportNotifier.php`: envia notificações de sucesso ou falha para o usuário.
- `app/Models/StudentExport.php`: representa o histórico e o estado das exportações.
- `app/Http/Controllers/Students/DownloadStudentExportController.php`: faz o download autenticado do arquivo gerado.
- `app/Support/BrazilianDocumentFormatter.php`: aplica máscaras de CPF, RG e CEP.
- `resources/views/filament/resources/user-resource/pages/student-exports-modal.blade.php`: modal com o histórico das últimas exportações.

## Por que usar fila e job

Exportar muitos registros dentro da própria requisição HTTP seria arriscado porque:

- o navegador poderia ficar aguardando por muito tempo;
- a requisição poderia estourar timeout;
- o processo PHP poderia consumir memória demais;
- a experiência do usuário ficaria travada;
- uma falha no meio do processo seria mais difícil de rastrear.

Com fila, o clique no botão apenas agenda o trabalho. O usuário recebe feedback imediato e pode continuar navegando no painel. O processamento pesado fica isolado em `ExportStudentsJob`, que pode ser monitorado, reprocessado e escalado separadamente em um ambiente real.

## Estratégia de performance

A exportação foi desenhada para não carregar todos os alunos em memória.

Em vez de usar Eloquent com relacionamentos carregados para cada aluno, a classe `StudentExportRowQuery` usa o Query Builder e processa blocos limitados de IDs de alunos. Para cada bloco, ela executa agregações SQL apenas sobre aquele conjunto de usuários.

Essa abordagem reduz:

- consumo de memória no PHP;
- risco de problema de N+1 queries;
- tempo ocioso da aplicação durante a exportação;
- quantidade de objetos Eloquent instanciados sem necessidade.

As principais agregações feitas no banco são:

- `MIN(ano_letivo)` para encontrar o primeiro ano de matrícula;
- `MAX(ano_letivo)` para encontrar o último ano de matrícula;
- `SUM(CASE WHEN resultado_final = ... THEN 1 ELSE 0 END)` para aprovações e reprovações;
- `ROW_NUMBER()` para identificar a matrícula mais recente e, com isso, a escola atual/mais recente do aluno.

Também foram adicionados índices compostos na tabela `matriculas` para apoiar as consultas mais importantes da exportação:

- índice para resumo por aluno, ano letivo e resultado final;
- índice para encontrar a matrícula mais recente por aluno.

## Escrita do arquivo XLSX

A geração da planilha usa `openspout/openspout`, uma biblioteca de escrita de arquivos XLSX em streaming. A escolha foi feita porque o desafio permite bibliotecas de manipulação de planilha, mas não permite usar o Exporter do Filament.

O streaming evita montar a planilha inteira em memória antes de salvar. O job escreve as linhas gradualmente em um arquivo temporário e, ao final, move o resultado para o disk configurado.

Por padrão, o arquivo fica no disk `local`, em uma área privada da aplicação. Isso evita expor a planilha diretamente em uma URL pública.

## Experiência do usuário

A interface foi pensada para deixar claro que a exportação pode demorar:

- a action **Exportar Excel** usa confirmação antes de iniciar;
- após o clique, o usuário recebe uma notificação informando que a exportação entrou na fila;
- quando o job termina, uma notificação persistente aparece com a ação **Baixar Excel**;
- a action **Últimas exportações** permite consultar o histórico sem sair da tela de alunos;
- o modal mostra status, quantidade de linhas processadas e link de download quando disponível.

Esse fluxo evita que o usuário fique preso em uma tela de carregamento e deixa o processamento assíncrono mais previsível.

## Segurança

O download é feito por uma rota autenticada. O controller verifica:

- se a exportação pertence ao usuário logado;
- se a exportação está concluída;
- se o arquivo existe no disk configurado.

Além disso, o arquivo é transmitido por stream. Isso evita depender de metadados do Flysystem, como tamanho do arquivo, e funciona melhor para arquivos privados armazenados localmente.

## Configuração

As principais opções podem ser ajustadas por variáveis de ambiente:

```dotenv
STUDENT_EXPORT_CHUNK_SIZE=1000
STUDENT_EXPORT_DISK=local
STUDENT_EXPORT_DIRECTORY=exports/students
STUDENT_EXPORT_QUEUE=default
DB_QUEUE_RETRY_AFTER=21600
```

Em produção, uma melhoria natural seria usar uma fila dedicada:

```dotenv
STUDENT_EXPORT_QUEUE=exports
```

Com isso, a aplicação poderia ter workers específicos para exportações, sem competir com outros jobs mais rápidos ou mais críticos.

## Testes

Foram adicionados testes para as partes principais da solução:

- formatação de CPF, RG e CEP;
- consulta agregada dos dados exportados;
- escrita do arquivo XLSX no disk configurado;
- download autenticado do arquivo gerado.

Para executar:

```bash
php artisan test
```

## Observações finais

A solução prioriza performance, rastreabilidade e boa experiência para o usuário. O histórico em `student_exports` permite saber quem solicitou a exportação, quando ela foi solicitada, qual é o status atual, quantas linhas foram processadas e se ocorreu alguma falha.

Em um ambiente maior, a mesma arquitetura permite evoluções como limpeza automática de arquivos antigos, tela dedicada de histórico de exportações, política de permissões mais granular e armazenamento em S3 ou outro serviço externo.
