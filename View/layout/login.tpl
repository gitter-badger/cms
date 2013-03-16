{include file="helpers/header.tpl"}
{literal}
<style type="text/css">
        /* Override some defaults */
    html, body {
        background-color: #444;
    }

    body {
        padding-top: 300px;
    }

    .container {
        width: 264px;
    }

        /* The white background content wrapper */
    .container > .content {
        background: #fff url('/vendor/Gratheon/CMS/assets/img/bg/01.jpg');
        padding: 20px;
        -webkit-border-radius: 10px 10px 10px 10px;
        -moz-border-radius: 10px 10px 10px 10px;
        border-radius: 10px 10px 10px 10px;
        -webkit-box-shadow: 0 1px 2px rgba(0, 0, 0, .15);
        -moz-box-shadow: 0 1px 2px rgba(0, 0, 0, .15);
        box-shadow: 0 1px 2px rgba(0, 0, 0, .15);
    }

    .login-form {

    }

    legend {
        margin-right: -50px;
        font-weight: bold;
        color: #404040;
    }

</style>
{/literal}
</head>
<body>
<div class="container">
    <div class="content">
        <div class="login-form">
            <img src="{$smarty.const.sys_url}vendor/Gratheon/CMS/assets/img/profile_login_logo.png" alt="{t}Spark{/t}" style="margin:0 auto 20px;display: block;"/>

            <form action='' method='post' name='formLogin'>
                <fieldset>
                    <div class="clearfix">
                        <input class='loginInput' type='text' name='login' value='' tabindex="1" id="login" placeholder="Username"/>
                    </div>
                    <div class="clearfix">
                        <input class='loginInput' type='password' name='pass' value='' tabindex="2" placeholder="Password"/>
                    </div>
                    <button class="btn primary pull-right" type="submit">Sign in</button>
                </fieldset>
            </form>
        </div>
    </div>
</div>
{literal}
<script>
    document.getElementById('login').focus();
</script>
{/literal}
</body>
</html>