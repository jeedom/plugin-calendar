Plug-in para criar uma agenda e acionar ações
(comando ou cenário).

Configuração do plugin
=======================

A configuração é muito simples, depois de baixar o plugin, ele
você acabou de ativá-lo e é isso.

Configuração do equipamento
=============================

A configuração dos dispositivos Calendar é acessível no menu
Plugins e organização.

Uma vez, você encontrará a lista da sua Agenda.

Aqui você encontra toda a configuração do seu equipamento :

-   **Nome de equipamentos** : nome do seu calendário.

-   **Objeto pai** : indica o objeto pai ao qual
    pertence a equipamento.

-   **Categoria** : categorias de equipamentos (pode pertencer a
    várias categorias).

-   **Activer** : torna seu equipamento ativo.

-   **Visible** : torna visível no painel.

-   **Widget, número de dias** : define o número de dias
    eventos a serem exibidos no widget.

-   **Número máximo de eventos** : defina o número máximo
    eventos a serem exibidos no painel.

-   **Não exibir status e pedidos
    ativação / desativação** : permite ocultar o status de
    a agenda, bem como os comandos para ativá-la ou não.

-   **Lista de eventos do calendário** : exibido abaixo do
    lista de todos os eventos do calendário (um clique nele permite
    editar o evento diretamente).

-   **Adicionar evento** : adicionar um evento ao calendário.

-   **Agenda** : Exibição de uma visualização do tipo de calendário com todos
    eventos em que você pode se mudar, escolha exibi-lo
    por semana ou dia, mova eventos (arrastar e soltar) e um
    clicar em um evento abrirá sua janela de edição.

Editando um evento
======================

Parte mais importante do plug-in, é aqui que você poderá
configure seu evento.

Evento
---------

Aqui você encontra :

-   **Nome do evento** : Nome do seu evento.

-   **ícone** : permite adicionar um ícone na frente do seu nome
    equipamento (para fazer isso, clique em "Escolha um ícone").

-   **Couleur** : permite escolher a cor do seu evento (uma
    marca de seleção também permite torná-lo transparente).

-   **Cor do texto** : permite escolher a cor do texto de
    seu evento.

-   **Não aparecer no painel** : permite não exibir
    este evento no widget.

Iniciar ação
---------------

Permite que você escolha as ações a serem executadas ao iniciar
o evento.

Para adicionar uma ação, basta clicar no botão + no final de
a linha, então você vai ter um botão para procurar um pedido de um
uma vez encontrado, você terá a opção de opções, se houver alguma. Você
pode adicionar quantas ações você quiser.

> **Tip**
>
> É possível modificar a ordem das ações mantendo / arrastando
> celle-ci


> **Tip**
>
>É possível executar as mesmas ações que nos cenários (consulte [aqui](https://jeedom.github.io/core/fr_FR/scenario))

Ação final
-------------

Igual à ação inicial, mas desta vez é a (s) ação (s) a
executar no final do evento.

Programmation
-------------

É aqui que todo o gerenciamento de tempo do seu evento está localizado :

-   **Começo** : Data de início do evento.

-   **Fin** : Data de término do evento.

-   **O dia inteiro** : permite definir o evento em qualquer
    o dia.

-   **Incluir por outro calendário** : Permite incluir outro
    evento no seu evento atual. Por exemplo, se você tiver um
    evento repetido toda segunda-feira e você inclui este
    evento A em seu evento atual, este será
    repetido automaticamente toda segunda-feira.

-   **Inclure** : permite forçar uma data de ocorrência, você pode
    coloque vários separando-os com, (vírgulas), você pode
    também define um intervalo com : (dois pontos).

-   **Repetida** : digamos que seu evento seja repetido (se isso
    caixa de seleção não estiver marcada, você não terá as seguintes opções).

-   **Modo de repetição** : permite especificar o modo de repetição,
    seja simples : todos os dias, todos os X dias ... ou repetição a cada
    1º, 2º… repetir um evento a cada 3ª segunda-feira do
    meses, por exemplo (as seguintes opções podem ser diferentes
    dependendo desta escolha).

-   **Repita cada** : \ [somente modo de repetição única \] permite
    defina a frequência de repetição do evento (por exemplo, a cada 3
    dias ou a cada 2 meses ...).

-   **Le** : \ [repita o modo o primeiro, o segundo ... somente \] :
    permite que você escolha um ensaio a cada 2ª segunda-feira do mês
    Por exemplo.

-   **Somente o** : permite restringir a repetição a certos
    dias úteis.

-   **Restriction** : permite restringir apenas o evento
    feriados ou excluir feriados.

-   **Jusqu'à** : fornece a data final da ocorrência do evento.

-   **Excluir por outro calendário** : permite excluir isso
    evento de acordo com outro calendário (para evitar, por exemplo, que
    2 eventos contraditórios são encontrados juntos).

-   **Exclure** : igual a "Incluir", mas desta vez para excluir
    datas.

> **Note**
>
> Os feriados são franceses e somente o francês não
> não funciona para outros países

> **Note**
>
> No canto superior direito, você tem 3 botões, um para excluir, um para
> salvar e um para duplicar. Ao clicar neste último jeedom
> exibe o evento resultante da duplicação para que você
> pode mudar o nome por exemplo.Então não esqueça de
> salvar após um clique no botão duplicado

Diário, pedidos e cenário
=============================

Uma agenda tem controles :

-   **Contínuo** : lista os eventos atuais separados por
    vírgulas, para uso no cenário mais simples e
    usar o operador contém (corresponde) ou não (não
    correspondências), por exemplo * \ [Apartamento \] \ [teste \] \ [Em andamento \] * corresponde
    "/ Anniv / ", será verdadeiro se na lista de eventos atuais houver
    um "Anniv"

- **Adicionar uma data** : permite a partir de um cenário adicionar uma data a um evento (tenha cuidado se você alterar o nome do evento, também será necessário corrigi-lo no cenário). Você pode colocar vários eventos separados por ,

- **Remover uma data** : permite que um cenário exclua uma data de um evento (tenha cuidado se você alterar o nome do evento, também será necessário corrigi-lo no cenário). Você pode colocar vários eventos separados por ,

> **Note**
>
> Você pode usar o comando "Em andamento" como gatilho
> em um cenário, cada atualização das informações acionará
> a execução do cenário. No entanto, é melhor usar este
> comando em um cenário programado com um teste no valor.
