<div style="display:flex;gap:10px;flex-wrap:wrap;margin-bottom:28px;">
  <a href="{{ route('mon-espace.admin.dashboard') }}" class="btn {{ request()->routeIs('mon-espace.admin.dashboard') ? 'btn-primary' : 'btn-ghost-dark' }} btn-sm">Vue d'ensemble</a>
  <a href="{{ route('mon-espace.admin.moderation.index') }}" class="btn {{ request()->routeIs('mon-espace.admin.moderation.*') ? 'btn-primary' : 'btn-ghost-dark' }} btn-sm">Modération des annonces</a>
  <a href="{{ route('mon-espace.admin.utilisateurs.index') }}" class="btn {{ request()->routeIs('mon-espace.admin.utilisateurs.*') ? 'btn-primary' : 'btn-ghost-dark' }} btn-sm">Utilisateurs</a>
</div>
