#!/usr/bin/env python3
# Read-only external HTTP connectivity diagnostics for the allowed Work Hub host.
import subprocess
urls = [
    "https://www.jobs.service.gov.uk/jobs/6ad095af813f93ba4140e36e",
    "https://jobs.service.gov.uk/jobs/6ad095af813f93ba4140e36e",
    "https://www.jobs.service.gov.uk/jobs/search?pageNumber=1",
]
for index, url in enumerate(urls,1):
    cmd=["/usr/bin/curl","--silent","--show-error","--max-time","12",
         "--max-redirs","0","--output","/dev/null",
         "--write-out","%{http_code}|%{content_type}|%{size_download}|%{redirect_url}",
         "--header","Accept: text/html",
         "--user-agent","JWTC-AdminJobImport/1.0",url]
    try:
        p=subprocess.run(cmd,stdout=subprocess.PIPE,stderr=subprocess.PIPE,
                         universal_newlines=True,timeout=15)
        fields=p.stdout.strip().split("|")
        status=fields[0] if fields else "unknown"
        content_type=fields[1] if len(fields)>1 else ""
        size=fields[2] if len(fields)>2 else ""
        redirect=fields[3] if len(fields)>3 else ""
        print("WORKHUB_REQUEST",index,"exit",p.returncode,"status",status,
              "mime",content_type[:64],"size",size,
              "redirect_same_host",redirect.startswith("https://www.jobs.service.gov.uk/")
                                    or redirect.startswith("https://jobs.service.gov.uk/"),
              "redirect_present",bool(redirect),"curl_error",bool(p.stderr.strip()),flush=True)
    except Exception as exc:
        print("WORKHUB_REQUEST",index,"exception",type(exc).__name__,flush=True)
