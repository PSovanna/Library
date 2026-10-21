<!doctype html>
<html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title>Library management</title>
<style>body{font:16px system-ui;background:#f3f6fa;color:#18283d;max-width:1150px;margin:40px auto;padding:0 20px}button,input,select,textarea{font:inherit;padding:10px;border:1px solid #bdcbdc;border-radius:6px}button{cursor:pointer;background:#e7eef8}button:disabled{opacity:.5}nav{display:flex;gap:10px;flex-wrap:wrap;margin:24px 0}section,dialog{background:white;padding:24px;border:1px solid #d3dfec;border-radius:12px}dialog{width:min(540px,85vw);max-height:85vh}dialog::backdrop{background:#15243988}label{display:block;margin:14px 0}input,select,textarea{display:block;box-sizing:border-box;width:100%;margin-top:6px}table{border-collapse:collapse;width:100%;font-size:14px}td,th{text-align:left;padding:12px;border-bottom:1px solid #dce3ec}td button{margin:3px}img{width:65px;height:65px;object-fit:cover;border-radius:6px}.scroll{overflow:auto}.error{color:#ac2020;white-space:pre-line}#status{white-space:pre-line;padding:12px 0;color:#176138}#status.error{color:#ac2020}.actions{display:flex;gap:10px;justify-content:flex-end}small{color:#52657c}</style></head>
<body><h1>Library management</h1><p>Manage authors, book types, books, and their author links.</p><nav id="tabs"></nav><p id="status" role="status" aria-live="polite"></p><section><div class="actions"><button id="add">Insert record</button><button id="reload">Refresh</button></div><h2 id="title"></h2><div class="scroll"><table><thead id="head"></thead><tbody id="rows"></tbody></table></div></section>
<dialog id="editor"><form id="form"><h2 id="form-title"></h2><p id="errors" class="error" role="alert"></p><div id="fields"></div><div class="actions"><button type="button" id="cancel">Return / Cancel</button><button id="save">Save</button></div></form></dialog>
<dialog id="deletion"><h2>Delete record?</h2><p id="delete-description"></p><p class="error" role="alert" id="delete-error"></p><div class="actions"><button id="delete-cancel">Return / Cancel</button><button id="delete-confirm">Delete</button></div></dialog>
<script>
const schemas = {
 'authors': {title:'Authors',key:['AuthorID'],fields:{AuthorName:['Author name','text',true,255],Gender:['Gender','text',false,20],DOB:['Date of birth','date'],POB:['Place of birth','text',false,255],Address:['Address','text',false,500],Phone:['Phone','tel',false,30],Email:['Email','email',false,255],Photo:['Picture','file']}},
 'book-types':{title:'Book types',key:['BookTypeID'],fields:{BookTypeName:['Book type name','text',true,255]}},
 'books':{title:'Books',key:['BookID'],fields:{BookTitle:['Book title','text',true,255],BookTypeID:['Book type','select',true,'book-types'],PublishDate:['Publish date','date'],NumOfPages:['Number of pages','number'],NumOfCopies:['Number of copies','number'],Edition:['Edition','text',false,100],Publisher:['Publisher','text',false,255],BookSource:['Book source','text',false,255],Remark:['Remark','textarea'],Photo:['Cover picture','file']}},
 'book-authors':{title:'Book authors',key:['BookID','AuthorID'],fields:{BookID:['Book','select',true,'books'],AuthorID:['Author','select',true,'authors'],AuthorDate:['Author date','date'],Remark:['Remark','textarea']}}
};
const $=id=>document.getElementById(id), api=@json(url('/api'));
let resource='authors', records=[], editing=null, pendingDelete=null, busy=false;
async function request(path,options={}) {
 let response;
 try { response=await fetch(api+'/'+path,{...options,headers:{Accept:'application/json',...options.headers}}); }
 catch { throw new Error('Cannot connect to the server. Check your connection and try again.'); }
 const data=await response.json().catch(()=>null);
 if(!response.ok || !data) throw new Error(data?.errors ? Object.entries(data.errors).map(([field,messages])=>field+': '+messages.join(' ')).join('\n') : data?.message || 'Request failed. Please try again.');
 return data;
}
function status(message,error=false){$('status').textContent=message;$('status').className=error?'error':'';}
function path(record){return resource+'/'+schemas[resource].key.map(key=>encodeURIComponent(record[key])).join('/');}
function cell(row,text){const td=document.createElement('td');td.textContent=text??'—';row.append(td);return td;}
async function load(){
 $('add').disabled=true;$('reload').disabled=true;
 try {
 records=await request(resource);$('title').textContent=schemas[resource].title;$('head').replaceChildren();$('rows').replaceChildren();
 const keys=[...schemas[resource].key,...Object.keys(schemas[resource].fields).filter(key=>!schemas[resource].key.includes(key))];
 const tr=document.createElement('tr');for(const key of [...keys,'Actions']){const th=document.createElement('th');th.textContent=key;tr.append(th);}$('head').append(tr);
 for(const record of records){const row=document.createElement('tr');for(const key of keys){const td=cell(row,key==='Photo'?'':record[key]);if(key==='Photo'&&record.PhotoUrl){const image=document.createElement('img');image.src=record.PhotoUrl;image.alt='Record picture';td.append(image);}}
 const actions=cell(row,'');for(const [label,fn] of [['Update',()=>openEditor(record)],['Delete',()=>openDelete(record)]]){const button=document.createElement('button');button.textContent=label;button.onclick=fn;actions.append(button);}$('rows').append(row);}
 if(!records.length){const row=document.createElement('tr');cell(row,'No records yet.').colSpan=keys.length+1;$('rows').append(row);}
 }catch(e){status(e.message,true);}finally{$('add').disabled=false;$('reload').disabled=false;}
}
async function openEditor(record=null){
 editing=record;$('fields').replaceChildren();$('errors').textContent='';$('form-title').textContent=(record?'Update ':'Insert ')+schemas[resource].title;
 $('editor').showModal();$('save').disabled=true;
 try{for(const [key,[label,type,required,limit]] of Object.entries(schemas[resource].fields)){
 const wrapper=document.createElement('label');wrapper.textContent=label;const input=document.createElement(type==='textarea'?'textarea':type==='select'?'select':'input');input.name=key;input.id='field-'+key;input.required=!!required;
 if(type==='select'){const empty=new Option('Select '+label.toLowerCase(),'');input.add(empty);const options=await request(limit);for(const item of options){const schema=schemas[limit];input.add(new Option(item[Object.keys(schema.fields)[0]],item[schema.key[0]]));}input.disabled=!!record&&resource==='book-authors';}
 else if(type!=='textarea'){input.type=type;if(type==='number'){input.min=0;input.max=4294967295;input.step=1;}if(type==='date'&&key==='DOB')input.max=new Date().toLocaleDateString('en-CA');if(type==='file')input.accept='image/jpeg,image/png,image/webp';if(type==='text'||type==='email'||type==='tel')input.maxLength=limit||255;}
 if(type!=='file')input.value=record?.[key]??'';
 wrapper.append(input);if(type==='file'){const hint=document.createElement('small');hint.textContent='JPG, PNG or WebP, maximum 2 MB. Leave blank to keep the existing picture.';wrapper.append(hint);
 const preview=document.createElement('img');preview.alt='Picture preview';preview.style.cssText='width:160px;height:160px;object-fit:contain;margin-top:12px';preview.hidden=!record?.PhotoUrl;
 if(record?.PhotoUrl)preview.src=record.PhotoUrl;
 const previewError=document.createElement('small');previewError.className='error';
 preview.onerror=()=>{preview.hidden=true;previewError.textContent='Picture could not be loaded. Please upload it again.';};
 let previewUrl=null;
 input.addEventListener('change',()=>{
 if(previewUrl){URL.revokeObjectURL(previewUrl);previewUrl=null;}
 previewError.textContent='';const file=input.files[0];
 if(file&&(file.size>2097152||!['image/jpeg','image/png','image/webp'].includes(file.type))){preview.hidden=true;input.setCustomValidity('Choose a JPG, PNG or WebP picture no larger than 2 MB.');previewError.textContent=input.validationMessage;return;}
 input.setCustomValidity('');
 const source=file?(previewUrl=URL.createObjectURL(file)):record?.PhotoUrl;
 preview.hidden=!source;if(source)preview.src=source;else preview.removeAttribute('src');
 });
 wrapper.append(preview,previewError);
 } $('fields').append(wrapper);
 }$('save').disabled=false;$('fields').querySelector('input,select,textarea')?.focus();}catch(e){$('errors').textContent=e.message;}
}
function setBusy(value){busy=value;for(const id of ['save','cancel','delete-confirm','delete-cancel'])$(id).disabled=value;}
$('form').onsubmit=async event=>{
 event.preventDefault();if(busy)return;$('errors').textContent='';const body=new FormData($('form'));
 for(const [key,value] of body){if(value instanceof File){if(!value.size){body.delete(key);continue;}if(value.size>2097152||!['image/jpeg','image/png','image/webp'].includes(value.type)){$('errors').textContent='Picture must be JPG, PNG or WebP and no larger than 2 MB.';return;}}}
 if(editing)body.set('_method','PATCH');setBusy(true);
 try{const data=await request(editing?path(editing):resource,{method:'POST',body});$('editor').close();status(data.message);await load();}catch(e){$('errors').textContent=e.message;}finally{setBusy(false);}
};
function openDelete(record){pendingDelete=record;$('delete-description').textContent='Delete '+schemas[resource].title+' record '+schemas[resource].key.map(key=>record[key]).join('/')+'? Related records must be removed first.';$('delete-error').textContent='';$('deletion').showModal();}
$('delete-confirm').onclick=async()=>{if(busy)return;setBusy(true);try{const data=await request(path(pendingDelete),{method:'DELETE'});$('deletion').close();status(data.message);await load();}catch(e){$('delete-error').textContent=e.message;}finally{setBusy(false);}};
$('cancel').onclick=()=>$('editor').close();$('delete-cancel').onclick=()=>$('deletion').close();for(const id of ['editor','deletion'])$(id).addEventListener('cancel',event=>{if(busy)event.preventDefault();});
for(const [key,schema] of Object.entries(schemas)){const button=document.createElement('button');button.textContent=schema.title;button.onclick=()=>{resource=key;status('');load();};$('tabs').append(button);}
$('add').onclick=()=>openEditor();$('reload').onclick=()=>load();load();
</script></body></html>
