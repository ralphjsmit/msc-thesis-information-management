---
title: Nested resources
---

- Define `getParentResource()` on the child, returning the parent class name or `ParentResource::asParent()`, which then allows you to chain `->relationship()` or `->inverseRelationship()` to customize those names
- Remove the `index` route from the child resource, as this is handed by the parent
- Define a `RelationManager` or `ManageRelatedRecords` page on the parent resource for the child. If it's a page, the route should use the kebab relationship name
- Page / RM does not need much as it can read from the resource, but define `$relatedResource` prop on it instead of form/table, and if it's a page you prob want the `CreateAction` in the page header instead of table header
- When generating link to fake "index" page for child resource, page with kebab relationship name on parent used first, then view page w/ relation manager, then edit page w/ relation manager, then index page of parent
