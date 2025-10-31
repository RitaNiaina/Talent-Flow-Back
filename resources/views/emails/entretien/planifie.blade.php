@component('mail::message')
# Bonjour {{ $entretien->candidature->candidat->nom_utilisateur }},

Votre entretien a été planifié avec succès pour l’offre **{{ $entretien->candidature->offre->titre_offre ?? 'Non spécifiée' }}**.

---

### 📅 Détails :
- **Date :** {{ \Carbon\Carbon::parse($entretien->date_entretien)->format('d/m/Y à H:i') }}
- **Type :** {{ ucfirst($entretien->type_entretien) }}

@if($entretien->type_entretien === 'en ligne' && $entretien->lien_meet)
👉 **Lien Google Meet :** [{{ $entretien->lien_meet }}]({{ $entretien->lien_meet }})
@elseif($entretien->type_entretien === 'présentiel' && $entretien->lieu)
📍 **Lieu :** {{ $entretien->lieu }}
@endif

@if($entretien->commentaire)
📝 **Commentaire du recruteur :**
> {{ $entretien->commentaire }}
@endif

---

Merci pour votre réactivité,  
**L’équipe RH de {{ $entretien->candidature->offre->recruteur->nom_utilisateur ?? 'l’entreprise' }}**

@endcomponent
