# main.py
from fastapi import FastAPI, HTTPException
from pydantic import BaseModel
from supabase import create_client, Client
import os
from dotenv import load_dotenv

# ----------------------------------------
# CONFIGURATION SUPABASE
# ----------------------------------------
load_dotenv()
SUPABASE_URL = os.getenv("SUPABASE_URL")
SUPABASE_KEY = os.getenv("SUPABASE_SERVICE_ROLE_KEY")
if not SUPABASE_URL or not SUPABASE_KEY:
    raise RuntimeError("🔑 Variables SUPABASE_URL et SUPABASE_SERVICE_ROLE_KEY manquantes")
supabase: Client = create_client(SUPABASE_URL, SUPABASE_KEY)

app = FastAPI(title="Obatis API", version="0.1.0")

# ----------------------------------------
# MODÈLES Pydantic
# ----------------------------------------
class ProfileUpdate(BaseModel):
    full_name: str

class CompanyCreate(BaseModel):
    name: str

class CreateCompanyPayload(BaseModel):
    name: str
    user_id: str

class InviteUser(BaseModel):
    email: str
    company_id: str
    role: str = "user"

class AcceptInvitePayload(BaseModel):
    user_id: str
    company_id: str

class UpdateRolePayload(BaseModel):
    user_id: str
    company_id: str
    new_role_id: int

class ProjectCreatePayload(BaseModel):
    name: str
    description: str
    company_id: str
    start_date: str  # ex. "2025-07-01"
    end_date: str    # ex. "2025-07-31"
    status: str = "draft"

class ProjectUpdatePayload(BaseModel):
    name: str | None = None
    description: str | None = None
    start_date: str | None = None
    end_date: str | None = None
    status: str | None = None

class MemberCreatePayload(BaseModel):
    user_id: str
    role_in_project: str | None = None

# ----------------------------------------
# ROUTES GÉNÉRALES
# ----------------------------------------
@app.get("/")
def home():
    return {"message": "Obatis API ready"}

# ----------------------------------------
# PROFIL UTILISATEUR
# ----------------------------------------
@app.get("/profile/{user_id}")
def get_profile(user_id: str):
    res = supabase.table("users").select("*").eq("id", user_id).single().execute()
    if not res.data:
        raise HTTPException(404, "Utilisateur non trouvé")
    return res.data

@app.put("/profile/{user_id}")
def update_profile(user_id: str, payload: ProfileUpdate):
    res = supabase.table("users")\
        .update({"full_name": payload.full_name})\
        .eq("id", user_id)\
        .execute()
    return {"message": "Profil mis à jour", "user": res.data}

# ----------------------------------------
# ENTREPRISES
# ----------------------------------------
@app.post("/companies")
def create_company(payload: CompanyCreate):
    res = supabase.table("companies").insert({"name": payload.name}).execute()
    return {"message": "Entreprise créée", "company": res.data}

@app.post("/create-company")
def create_company_with_admin(payload: CreateCompanyPayload):
    # 1) création de l'entreprise
    comp = supabase.table("companies").insert({"name": payload.name}).execute().data[0]
    # 2) création du membership admin
    supabase.table("memberships").insert({
        "user_id": payload.user_id,
        "company_id": comp["id"],
        "role_id": 1,         # 1 = admin
        "status": "active"
    }).execute()
    return {"message": "Entreprise + admin créés", "company": comp}

# ----------------------------------------
# INVITATIONS & ACCEPTATION
# ----------------------------------------
@app.post("/invite-user")
def invite_user(payload: InviteUser):
    # 1) création/user exist check dans Auth
    try:
        auth_res = supabase.auth.admin.create_user({"email": payload.email})
        user_id = auth_res.user.id
        # insertion dans table users locale
        supabase.table("users").insert({"id": user_id, "email": payload.email}).execute()
    except Exception:
        # si existe déjà, on récupère l'ID
        exist = supabase.table("users").select("id").eq("email", payload.email).single().execute()
        if not exist.data:
            raise HTTPException(400, "Impossible de récupérer l'utilisateur existant")
        user_id = exist.data["id"]

    # 2) vérif membership existant
    m = supabase.table("memberships")\
        .select("*")\
        .eq("user_id", user_id)\
        .eq("company_id", payload.company_id)\
        .execute()
    if m.data:
        raise HTTPException(400, "Déjà invité / membre de cette entreprise")

    # 3) création du membership en pending
    supabase.table("memberships").insert({
        "user_id": user_id,
        "company_id": payload.company_id,
        "role_id": 2 if payload.role == "user" else 1,  # adapter selon mapping
        "status": "pending"
    }).execute()

    return {"message": f"Invitation envoyée à {payload.email}"}

@app.post("/accept-invite")
def accept_invite(payload: AcceptInvitePayload):
    supabase.table("memberships")\
        .update({"status": "active"})\
        .eq("user_id", payload.user_id)\
        .eq("company_id", payload.company_id)\
        .execute()
    return {"message": "Invitation acceptée"}

# ----------------------------------------
# MISE À JOUR DE RÔLE
# ----------------------------------------
@app.put("/memberships/update-role")
def update_role(payload: UpdateRolePayload):
    # TODO : vérifier que l'appelant est admin (auth.uid()) sur payload.company_id
    supabase.table("memberships")\
        .update({"role_id": payload.new_role_id})\
        .eq("user_id", payload.user_id)\
        .eq("company_id", payload.company_id)\
        .execute()
    return {"message": f"Rôle mis à jour en {payload.new_role_id}"}

# ----------------------------------------
# PROJETS (CRUD)
# ----------------------------------------
@app.get("/projects")
def list_projects():
    # renvoie TOUS les projets visibles par l'utilisateur (selon RLS)
    res = supabase.table("projects").select("*").execute()
    return {"projects": res.data}

@app.post("/projects")
def create_project(payload: ProjectCreatePayload):
    res = supabase.table("projects").insert({
        "name": payload.name,
        "description": payload.description,
        "company_id": payload.company_id,
        "start_date": payload.start_date,
        "end_date": payload.end_date,
        "status": payload.status
    }).execute()
    return {"message": "Projet créé", "project": res.data}

@app.put("/projects/{project_id}")
def update_project(project_id: str, payload: ProjectUpdatePayload):
    body = {k: v for k, v in payload.dict().items() if v is not None}
    res = supabase.table("projects").update(body).eq("id", project_id).execute()
    return {"message": "Projet mis à jour", "project": res.data}

@app.delete("/projects/{project_id}")
def delete_project(project_id: str):
    supabase.table("projects").delete().eq("id", project_id).execute()
    return {"message": "Projet supprimé"}

# ----------------------------------------
# MEMBRES DE PROJET
# ----------------------------------------
@app.get("/projects/{project_id}/members")
def list_project_members(project_id: str):
    res = supabase.table("project_members")\
        .select("*")\
        .eq("project_id", project_id)\
        .execute()
    return {"members": res.data}

@app.post("/projects/{project_id}/members")
def add_project_member(project_id: str, payload: MemberCreatePayload):
    res = supabase.table("project_members").insert({
        "project_id": project_id,
        "user_id": payload.user_id,
        "role_in_project": payload.role_in_project
    }).execute()
    return {"message": "Membre ajouté", "member": res.data}

@app.delete("/projects/{project_id}/members/{user_id}")
def remove_project_member(project_id: str, user_id: str):
    supabase.table("project_members")\
        .delete()\
        .eq("project_id", project_id)\
        .eq("user_id", user_id)\
        .execute()
    return {"message": "Membre retiré"}

from fastapi import FastAPI

app = FastAPI()

@app.get("/testroute")
async def testroute():
    return {"status": "ok", "message": "API Obatis opérationnelle !"}
