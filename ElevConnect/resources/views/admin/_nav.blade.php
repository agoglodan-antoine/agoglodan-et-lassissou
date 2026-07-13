<div style="display:flex;gap:10px;flex-wrap:wrap;margin-bottom:28px;">
  <a href="{{ route('admin.dashboard') }}" class="btn {{ request()->routeIs('admin.dashboard') ? 'btn-primary' : 'btn-ghost-dark' }} btn-sm">Vue d'ensemble</a>
  <a href="{{ route('admin.moderation.index') }}" class="btn {{ request()->routeIs('admin.moderation.*') ? 'btn-primary' : 'btn-ghost-dark' }} btn-sm">Modération des annonces</a>
  <a href="{{ route('admin.litiges.index') }}" class="btn {{ request()->routeIs('admin.litiges.*') ? 'btn-primary' : 'btn-ghost-dark' }} btn-sm">Litiges</a>
  <a href="{{ route('admin.utilisateurs.index') }}" class="btn {{ request()->routeIs('admin.utilisateurs.*') ? 'btn-primary' : 'btn-ghost-dark' }} btn-sm">Utilisateurs</a>
</div>
