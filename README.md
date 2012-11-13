XP Framework based OAuth2 client
===
Using the examples:

GoogleUserInfo
--
Display information for the logged in Google user after authenticating:

1. First go to auth URL:
```sh
$ xpcli -cp examples GoogleUserInfo -cid XXXXXXXXXXXX.apps.googleusercontent.com -sec <clientsecret>
Redirecting ...
Go to:
https://accounts.google.com/o/oauth2/auth?response_type=code&redirect_uri=urn%3Aietf%3Awg%3Aoauth%3A2.0%3Aoob&client_id=XXXXXXXXXXXX.apps.googleusercontent.com&scope=https%3A%2F%2Fwww.googleapis.com%2Fauth%2Fuserinfo.profile+https%3A%2F%2Fwww.googleapis.com%2Fauth%2Fuserinfo.email&access_type=&approval_prompt=force
```

2. Enter received code:
```sh
kiesel@lostlap [13:10:44] [~/dev/oauth] [master *]
-> % xpcli -cp examples GoogleUserInfo -cid XXXXXXXXXXXX.apps.googleusercontent.com -sec <clientsecret> -c '4/pRUi24dxJHVaaNE5mst4Fut3D5AM.En2D6Z6L624fOl05ti8ZT3Y7eV_zdQI'
Acquiring accessToken...
Token:
{ "access_token" : "<accessToken>" , "token_type" : "Bearer" , "expires_in" : 3600 , "id_token" : "<long-token-id>" , "refresh_token" : "<refresh-token-id>" , "created" : 1352808662 }
```

3. Perform actual authorized request w/ given accessToken:
```sh
kiesel@lostlap [13:11:02] [~/dev/oauth] [master *]
-> % xpcli -cp examples GoogleUserInfo -cid XXXXXXXXXXXX.apps.googleusercontent.com -sec <clientsecret> -t '{ "access_token" : "<accessToken>" , "token_type" : "Bearer" , "expires_in" : 3600 , "id_token" : "<long-token-id>" , "refresh_token" : "<refresh-token-id>" , "created" : 1352808662 }'
Performing authorized action...
[
  id => "10961431..."
  email => "kiesel@..."
  verified_email => true
  name => "Alex Kiesel"
  given_name => "Alex"
  family_name => "Kiesel"
  link => "https://plus.google.com/10961431..."
  picture => "https://lh5.googleusercontent.com/-oLB1rrTuyD0/AAAAAAAAAAA/AAAAAAAAAA/AAAAAAAAAA/photo.jpg"
  gender => "male"
  locale => "en"
]
```

GithubUser
--
Display information about currently logged in user

1. First go to auth URL:
```sh
kiesel@lostlap [14:14:53] [~/dev/oauth] [master *]
-> % xpcli -cp examples GithubUser -cid <client-id> -sec <client-secret>
Redirecting ...
Go to:
https://github.com/login/oauth/authorize?response_type=code&redirect_uri=&client_id=<client-id>&scope=user&access_type=&approval_prompt=force
```

2. Convert code into accessToken
```sh
kiesel@lostlap [14:14:54] [~/dev/oauth] [master *]
-> % xpcli -cp examples GithubUser -cid <client-id> -sec <client-secret> -c 1e201539cc0cb138adf3
Acquiring accessToken...
Token:
{ "token_type" : "bearer" , "access_token" : "<access-token>" , "created" : 1352812514 }
```

3. Use accessToken to perform actual request on behalf of the user:
```sh
kiesel@lostlap [14:15:14] [~/dev/oauth] [master *]
-> % xpcli -cp examples GithubUser -cid <client-id> -sec <client-secret> -t '{ "token_type" : "bearer" , "access_token" : "<access-token>" , "created" : 1352812514 }'
Performing authorized action...
User:
[
  owned_private_repos => 0
  public_repos => 12
  type => "User"
  following => 8
  created_at => "2009-09-16T14:58:16Z"
  bio => null
  public_gists => 5
  blog => null
  gravatar_id => "66552a028d39afd16f21858d7ac9cf41"
  private_gists => 0
  collaborators => 0
  plan => [
    space => 307200
    collaborators => 0
    private_repos => 0
    name => "free"
  ]
  company => null
  email => "alex@..."
  followers => 12
  location => null
  avatar_url => "https://secure.gravatar.com/avatar/66552a028d39afd16f21858d7ac9cf41?d=https://a248.e.akamai.net/assets.github.com%2Fimages%2Fgravatars%2Fgravatar-user-420.png"
  name => "Alex Kiesel"
  url => "https://api.github.com/users/kiesel"
  total_private_repos => 0
  html_url => "https://github.com/kiesel"
  id => 127769
  disk_usage => 1924
  hireable => false
  login => "kiesel"
]
```