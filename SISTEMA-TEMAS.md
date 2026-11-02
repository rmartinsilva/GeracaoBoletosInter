# Sistema de Temas - Painel Administrativo

Este documento explica como funciona o sistema de modo claro/escuro implementado no painel administrativo.

## 🎨 Visão Geral

O sistema permite que cada usuário escolha entre tema **claro** e **escuro**, com a preferência salva no banco de dados e aplicada automaticamente em todas as sessões.

## 📁 Arquivos Principais

### Banco de Dados
- **`database/migrations/2025_10_06_201213_create_configuracao_usuario_table.php`**
  - Migração para tabela `configuracao_usuario`
  - Campos: `usuario_id`, `tema`, `preferencias`

### Modelos
- **`app/Models/ConfiguracaoUsuario.php`**
  - Modelo para gerenciar configurações do usuário
  - Relacionamento com `Usuario`
  - Métodos auxiliares: `isDarkMode()`, `getTema()`, `setTema()`

- **`app/Models/Usuario.php`** (atualizado)
  - Adicionado relacionamento `configuracao()`
  - Métodos: `getConfiguracao()`, `isDarkMode()`, `getTema()`

### Controllers
- **`app/Http/Controllers/Painel/ConfiguracaoController.php`**
  - `toggleTema()` - Altera tema via AJAX
  - `getConfiguracao()` - Retorna configuração atual
  - `setConfiguracao()` - Atualiza configurações específicas

### Views
- **`resources/views/template/painel.blade.php`** (atualizado)
  - Botão de toggle na topbar
  - Aplicação automática do tema baseado na configuração
  - JavaScript para controle de tema

### CSS
- **`public/assets/css/painel.css`** (atualizado)
  - Variáveis CSS para modo escuro
  - Classes específicas para tema escuro
  - Transições suaves entre temas

## 🎯 Como Funciona

### 1. **Detecção Inicial**
```php
// No template painel.blade.php
<body data-tema="{{ Auth::user()->getTema() }}">
```

### 2. **Aplicação do Tema**
```javascript
// JavaScript aplica o tema baseado no data-tema
function aplicarTema(tema) {
    if (tema === 'escuro') {
        body.classList.add('tema-escuro');
    } else {
        body.classList.remove('tema-escuro');
    }
}
```

### 3. **Toggle via AJAX**
```javascript
// Requisição para alterar tema
const response = await fetch('/painel/configuracao/toggle-tema', {
    method: 'POST',
    headers: {
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
    }
});
```

### 4. **Persistência**
```php
// Salva no banco de dados
$configuracao->setTema($novoTema);
```

## 🎨 Sistema de Cores

### Variáveis CSS - Tema Claro (Padrão)
```css
:root {
    --painel-primary: #88B04B;        /* Verde principal */
    --painel-secondary: #5D4037;      /* Marrom textos */
    --painel-bg-primary: #FFFFFF;     /* Fundo branco */
    --painel-bg-secondary: #F8F9FA;   /* Fundo cinza claro */
}
```

### Variáveis CSS - Tema Escuro
```css
:root {
    --painel-dark-primary: #88B04B;   /* Verde preservado */
    --painel-dark-secondary: #5D4037; /* Marrom preservado */
    --painel-dark-bg-primary: #1F2937;    /* Fundo escuro */
    --painel-dark-bg-secondary: #374151;  /* Fundo secundário */
    --painel-dark-text-primary: #F9FAFB;  /* Texto claro */
}
```

## 🚀 Recursos Implementados

### ✅ **Funcionalidades**
- [x] Toggle entre tema claro e escuro
- [x] Persistência da preferência no banco
- [x] Aplicação automática baseada na configuração
- [x] Transições suaves entre temas
- [x] Botão flutuante na topbar
- [x] Ícones animados (sol/lua)
- [x] Sistema de configuração extensível

### ✅ **Componentes Visuais**
- [x] Sidebar com tema escuro
- [x] Topbar com tema escuro
- [x] Área de conteúdo com tema escuro
- [x] Formulários com tema escuro
- [x] Botões com tema escuro
- [x] Sistema UsinaWeb com tema escuro
- [x] Alertas com tema escuro

## 📋 Como Usar

### Para o Usuário Final
1. **Login** no painel administrativo
2. **Clicar no botão** de toggle (sol/lua) na topbar
3. **Tema alterna automaticamente**
4. **Preferência é salva** para próximas sessões

### Para Desenvolvedores
1. **Acessar configuração:** `$usuario->getTema()`
2. **Verificar modo escuro:** `$usuario->isDarkMode()`
3. **Alterar tema:** `$usuario->getConfiguracao()->setTema('escuro')`

## 🔧 Personalização

### Adicionar Novas Variáveis CSS
```css
/* No arquivo painel.css */
:root {
    --sua-nova-cor: #FF6B35;
}

.tema-escuro {
    --sua-nova-cor-escura: #E55A2B;
}
```

### Estender Configurações
```php
// Adicionar nova configuração
$configuracao->setPreferencia('fonte_tamanho', 'grande');
$configuracao->getPreferencia('fonte_tamanho', 'medio');
```

## 🎯 Benefícios

1. **🎨 Experiência Personalizada** - Cada usuário escolhe seu tema preferido
2. **👁️ Acessibilidade** - Tema escuro reduz fadiga visual
3. **💾 Persistência** - Preferência salva no banco de dados
4. **⚡ Performance** - Aplicação instantânea via CSS
5. **🔧 Manutenibilidade** - Sistema centralizado e organizado
6. **🚀 Escalabilidade** - Fácil extensão para novas configurações

## 📈 Próximos Passos

- [ ] **Tema automático** baseado na hora do dia
- [ ] **Detecção do sistema** (prefere-color-scheme)
- [ ] **Mais opções de cores** (azul, roxo, etc.)
- [ ] **Configurações avançadas** (fonte, espaçamento, animações)
- [ ] **Temas pré-definidos** por segmentos (vendas, financeiro, etc.)

## 🔍 Troubleshooting

### Problema: Tema não aplica
**Solução:** Verificar se o usuário tem configuração criada
```php
// Criar configuração manualmente
Auth::user()->getConfiguracao();
```

### Problema: Botão não funciona
**Solução:** Verificar se o usuário está autenticado e se a rota está acessível

### Problema: CSS não carrega
**Solução:** Verificar se o arquivo `painel.css` está sendo incluído
```html
<link rel="stylesheet" href="{{ url('/assets/css/painel.css') }}">
```

---

## 🎉 Sistema Implementado com Sucesso!

O sistema de temas está totalmente funcional e pronto para uso. Cada usuário pode personalizar sua experiência visual no painel administrativo, com a preferência sendo automaticamente aplicada em todas as sessões futuras.
