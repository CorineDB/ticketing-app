Yes, in `src/views/Events/EventDetailView.vue`, the content for each tab is indeed kept separate. Each tab has its own dedicated `<template #[tab.id]>` slot where its specific content (components, HTML) is rendered.

For example:
*   The 'Overview' tab has its content defined directly within `<template #overview>`.
*   The 'Ticket Types' tab has its content within `<template #ticket-types>`.
*   The 'Gates' tab has its content within `<template #gates>`.
*   The 'Statistics' tab uses the `<EventStats>` component within its `<template #statistics>` slot.

While the content is separate, some data (like event-specific `ticketTypes`, `gates`, and `statistics`) is fetched together when the `loadEvent()` function runs, usually when the component is mounted. This means that even if a tab isn't immediately visible, its data might already be loaded in the background.