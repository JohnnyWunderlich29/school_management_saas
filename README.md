🚀 Sistema Integrado de Gestão Escolar (SigeEdu)
Este é um ERP robusto desenvolvido em Laravel para gestão completa de instituições de ensino, utilizando arquitetura MVC e isolamento de dados por unidade escolar (ID)

📌 Módulos do Sistema
O sistema é dividido em módulos interdependentes para garantir a integridade dos dados:Módulo
💰 Financeiro Gestão de receitas e despesas.Geração de boletos, controle de inadimplência, fluxo de caixa e gateway de pagamentos.
👨‍🏫 FuncionáriosGestão de RH e corpo docente. Cadastro de contratos, atribuição de turmas, folha de pagamento e permissões (ACL)
🎓 AlunosGestão da vida acadêmica. Matrículas, boletins, frequência, histórico escolar e portal do aluno.
🏫 EscolasGestão multi-unidade.Cadastro de unidades, salas, laboratórios, anos letivos e matriz curricular.

🛠️ Tecnologias UtilizadasBackend:
PHP 8.2.1 + Laravel Framework
Banco de Dados: PostgresSQL
Frontend: Tailwind CSS / 
Integração Financeira: API de Boletos (Ex: Asaas)

🏗️ Arquitetura e Segurança
O projeto foi desenhado focando em escalabilidade e segurança de dados:

Padrão MVC: Organização clara entre Modelos, Visões e Controladores para facilitar a manutenção.

Multi-tenancy (Banco Compartilhado): Todas as escolas residem no mesmo banco de dados, sendo diferenciadas pela coluna school_id nas tabelas principais.

Isolamento via Middleware: O acesso aos dados é restrito por um Middleware customizado (SchoolScopeMiddleware). Este componente garante que um usuário autenticado só visualize e manipule registros pertencentes à sua escola (school_id), prevenindo vazamento de dados entre instituições.

Global Scopes: Utilização de Eloquent Global Scopes para filtrar automaticamente todas as queries pelo ID da escola ativa na sessão.
