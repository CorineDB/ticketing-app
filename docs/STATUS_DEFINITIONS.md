# Définitions des Statuts

Ce document décrit les différents statuts utilisés dans l'application de ticketing.

## Statuts des Événements (EventStatus)

Les événements peuvent avoir les statuts suivants :

| Statut | Description | Couleur |
|--------|-------------|---------|
| `draft` | Événement en brouillon, non publié | Gris |
| `published` | Événement publié et visible au public | Bleu |
| `ongoing` | Événement en cours | Vert (avec animation pulse) |
| `completed` | Événement terminé | Violet |
| `cancelled` | Événement annulé | Rouge |

### Flux de statuts typique

```
draft → published → ongoing → completed
                         ↓
                    cancelled
```

### Règles métier

- Un événement commence toujours en statut `draft`
- Seuls les événements `published` sont visibles au public
- Le statut `ongoing` est généralement mis à jour automatiquement quand l'événement commence
- Le statut `completed` est mis à jour quand l'événement se termine
- Un événement peut être `cancelled` à tout moment avant sa complétion

## Statuts des Tickets (TicketStatus)

Les tickets peuvent avoir les statuts suivants :

| Statut | Description | Couleur |
|--------|-------------|---------|
| `issued` | Ticket émis mais non réservé | Gris |
| `reserved` | Ticket réservé temporairement | Jaune |
| `paid` | Ticket payé et confirmé | Vert |
| `in` | Détenteur du ticket est entré dans l'événement | Bleu (avec animation pulse) |
| `out` | Détenteur du ticket est sorti de l'événement | Violet |
| `invalid` | Ticket invalide ou révoqué | Rouge |
| `refunded` | Ticket remboursé | Orange |

### Flux de statuts typique

```
issued → reserved → paid → in → out
           ↓         ↓      ↓
        invalid  refunded  invalid
```

### Règles métier

- Un ticket commence en statut `issued` lors de sa création
- Le statut `reserved` est temporaire (généralement 15-30 minutes)
- Le statut `paid` confirme le paiement et l'acquisition du ticket
- Les statuts `in` et `out` sont utilisés pour le contrôle d'accès
- Le passage de `in` à `out` n'est possible que si `allow_reentry` est activé sur l'événement
- Un ticket `refunded` ou `invalid` ne peut plus être utilisé

## Statuts des Portes/Gates (GateStatus)

Les portes peuvent avoir les statuts suivants :

| Statut | Description |
|--------|-------------|
| `active` | Porte active et opérationnelle |
| `pause` | Porte temporairement en pause |
| `inactive` | Porte inactive |

## Statuts des Commandes (OrderStatus)

Les commandes peuvent avoir les statuts suivants :

| Statut | Description | Couleur |
|--------|-------------|---------|
| `pending` | Commande en attente de paiement | Jaune (avec animation pulse) |
| `completed` | Commande complétée avec succès | Vert |
| `failed` | Échec de la commande | Rouge |
| `cancelled` | Commande annulée | Gris |
| `refunded` | Commande remboursée | Orange |

## Composants UI

### Utilisation des badges de statut

#### Pour les événements
```vue
<EventStatusBadge :status="event.status" />
```

#### Pour les tickets
```vue
<TicketStatusBadge :status="ticket.status" />
```

#### Badge générique
```vue
<StatusBadge
  :status="myStatus"
  :type="'event' | 'ticket' | 'order'"
  :label="'Libellé personnalisé'"
  :show-dot="true"
/>
```
