import smtpd

import smtplib

import asyncore

class SMTPServer(smtpd.SMTPServer):

    def __init__(*args, **kwargs):
        print("Running fake smtp server on port 25")
        smtpd.SMTPServer.__init__(*args, **kwargs)

    def process_message(*args, **kwargs):
        to = args[3][0]
        msg = args[4]
        gmail_user = 'yourgmailhere'
        gmail_pwd = 'yourgmailpassword'
        smtpserver = smtplib.SMTP("smtp.gmail.com",587)
        smtpserver.ehlo()
        smtpserver.starttls()
        smtpserver.ehlo
        smtpserver.login(gmail_user, gmail_pwd)
        smtpserver.sendmail(gmail_user, to, msg)
        print('sent to '+to)
        pass

if __name__ == "__main__":
    smtp_server = SMTPServer(('localhost', 25), None)
    try:
        asyncore.loop()
    except KeyboardInterrupt:
        smtp_server.close()
