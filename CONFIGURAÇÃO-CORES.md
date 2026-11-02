# Sistema de Cores - Painel Administrativo

Este documento explica como funciona o sistema de cores padronizado do painel administrativo e como alterar as cores para diferentes clientes.

## 📁 Arquivo Principal: `public/assets/css/painel.css`

O arquivo `painel.css` contém todas as variáveis CSS e estilos padronizados para o painel administrativo.

## 🎨 Paleta de Cores

### Variáveis CSS Definidas

```css
:root {
    /* Cores Primárias */
    --painel-primary: #88B04B;        /* Verde principal (botões, destaques) */
    --painel-primary-dark: #6B8E3A;   /* Verde escuro (hover) */
    --painel-primary-light: #A5C970;  /* Verde claro (fundos suaves) */

    /* Cores Secundárias */
    --painel-secondary: #5D4037;      /* Marrom (textos, títulos) */
    --painel-secondary-light: #8B6F47; /* Marrom claro */
    --painel-secondary-dark: #3E2723;  /* Marrom escuro */

    /* Sistema UsinaWeb */
    --usinaweb-bg: linear-gradient(to right, #1e3a8a, #3730a3, #1e1b4b);
    --usinaweb-border: rgba(59, 130, 246, 0.3);
    --usinaweb-text: #ffffff;
    --usinaweb-text-secondary: #dbeafe;
}
```

## 🔄 Como Alterar as Cores para um Novo Cliente

### Passo 1: Definir as Novas Cores
No arquivo `public/assets/css/painel.css`, altere as variáveis CSS:

```css
:root {
    /* Cores Primárias do NOVO CLIENTE */
    --painel-primary: #NOVA_COR_PRINCIPAL;        /* Ex: #FF6B35 */
    --painel-primary-dark: #NOVA_COR_ESCURA;      /* Ex: #E55A2B */
    --painel-primary-light: #NOVA_COR_CLARA;      /* Ex: #FF8A5E */

    /* Cores Secundárias do NOVO CLIENTE */
    --painel-secondary: #NOVA_COR_SECUNDARIA;     /* Ex: #2C3E50 */
    --painel-secondary-light: #NOVA_COR_CLARA;    /* Ex: #34495E */
    --painel-secondary-dark: #NOVA_COR_ESCURA;    /* Ex: #1A252F */
}
```

### Passo 2: Atualizar Logo do Cliente
- Substitua a imagem em `public/assets/imagens/logo-cliente-painel.png`
- Ajuste o tamanho se necessário na classe `.painel-logo-cliente`

### Passo 3: Personalizar Sistema UsinaWeb (Opcional)
Se necessário, ajuste as cores do sistema UsinaWeb:

```css
:root {
    --usinaweb-bg: linear-gradient(to right, #SUA_COR1, #SUA_COR2, #SUA_COR3);
    --usinaweb-border: rgba(59, 130, 246, 0.3);
}
```

## 📋 Lista de Classes CSS Disponíveis

### Layout e Estrutura
- `.painel-container` - Container principal com fundo gradiente
- `.painel-login-card` - Card do formulário de login
- `.painel-title` - Título principal

### Formulários
- `.painel-label` - Labels dos campos
- `.painel-input-container` - Container do input com ícone
- `.painel-input-icon` - Ícones dos inputs
- `.painel-input` - Campos de entrada
- `.painel-password-toggle` - Botão toggle de senha
- `.painel-checkbox` - Checkbox "Lembrar-me"
- `.painel-checkbox-label` - Label do checkbox
- `.painel-btn-primary` - Botão principal
- `.painel-btn-icon` - Ícones dos botões

### Estados e Efeitos
- `.painel-focus-ring` - Focus state dos inputs
- `.painel-hover-lift` - Efeito hover nos botões
- `.painel-transition` - Transições gerais

### Rodapé
- `.painel-footer` - Container do rodapé
- `.painel-footer-text` - Texto do rodapé
- `.usinaweb-container` - Container da logo UsinaWeb
- `.usinaweb-logo-wrapper` - Wrapper da logo com fundo azul
- `.usinaweb-logo` - Logo UsinaWeb

### Utilitários
- `.text-painel-primary` - Texto com cor primária
- `.bg-painel-primary` - Fundo com cor primária
- `.border-painel-primary` - Borda com cor primária

## 🚀 Benefícios do Sistema

1. **Manutenção Fácil** - Alterar cores em um único lugar
2. **Consistência Visual** - Todas as telas seguem o mesmo padrão
3. **Flexibilidade** - Fácil adaptação para diferentes clientes
4. **Performance** - CSS otimizado e bem estruturado
5. **Escalabilidade** - Sistema preparado para expansão

## 📝 Exemplo de Uso

Para usar em novas páginas do painel:

```html
<!DOCTYPE html>
<html>
<head>
    <link rel="stylesheet" href="{{ url('/assets/css/painel.css') }}">
</head>
<body class="painel-gradient-bg">
    <div class="painel-container">
        <div class="painel-login-card">
            <h1 class="painel-title">Minha Nova Página</h1>
            <button class="painel-btn-primary">Meu Botão</button>
        </div>
    </div>
</body>
</html>
```

## 🎯 Próximos Passos

- [ ] Documentar todas as cores utilizadas no projeto
- [ ] Criar temas pré-definidos para diferentes segmentos
- [ ] Implementar modo dark/light theme
- [ ] Adicionar variáveis para espaçamentos e tipografia
